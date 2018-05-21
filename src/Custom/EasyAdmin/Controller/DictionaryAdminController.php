<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.12.2017
 * Time: 17:27
 */
namespace Custom\EasyAdmin\Controller;

use AppBundle\Entity\Dictionary;
use AppBundle\Entity\DictionaryProcessing;
use AppBundle\Entity\Word;
use AppBundle\Entity\WordAttribute;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\PessimisticLockException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Custom\EasyAdmin\WordHandler\Event\AbortableEvent;
use Custom\EasyAdmin\WordHandler\Event\TryLoadEvent;
use Custom\EasyAdmin\WordHandler\Event\WaitingEvent;
use Custom\EasyAdmin\WordHandler\Exception\AbortedException;

class DictionaryAdminController extends BaseAdminController {

    private $processingInfo;
    private $dictionary;

    protected function initialize(Request $request)
    {
        $this->em = $this->getDoctrine()->getmanager();
        return parent::initialize($request);
    }

    protected function newDictionaryAction(){
        do{
            try{
                return $this->newAction();
            }
            catch(UniqueConstraintViolationException $ex){
                dump($ex);return new Response();
                usleep(rand(1000,1000000));
                $this->em = $this->getDoctrine()->resetmanager();
                continue;
            }
        }while(true);
    }

    protected function editDictionaryAction(){
        do{
            try{
                return $this->editAction();
            }
            catch(UniqueConstraintViolationException $ex){
                usleep(rand(1000,1000000));
                $this->em = $this->getDoctrine()->resetmanager();
                continue;
            }
        }while(true);
    }

    protected function prePersistDictionaryEntity(Dictionary $dictionary){
        $this->preSaveDictionaryEntity($dictionary);
    }

    protected function preUpdateDictionaryEntity(Dictionary $dictionary){
        $this->preSaveDictionaryEntity($dictionary);
    }
    protected function preSaveDictionaryEntity(Dictionary $dictionary){
        $dictionary->getProcessingInfo()->setProcessed(0);
        $dictionary->getProcessingInfo()->setTotal($dictionary->getWords()->count());
        $dictionary->getProcessingInfo()->setStatus(DictionaryProcessing::STATUS_PENDING);
    }

    /**
     * @param $id
     * @return mixed|Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @Route("/dictionary/process/{id}")
     * @Method({"POST", "GET"})
     */
    public  function processDictionaryAction(Request $request, $id){
        $this->request = $request;
        $this->em = $this->getDoctrine()->getManager();
        if($this->request->getSession()->isStarted()){
            $this->request->getSession()->save();
        }
        $action = $this->request->query->get('action', 'status'); // start, status, pause, resume
        if($this->request->getMethod()!=Request::METHOD_POST) $action = 'status';

        $this->dictionary = $this->em->getRepository(Dictionary::class)
            ->findWithProcessingInfo($id);

        if(is_null($this->dictionary))
            return new Response('dictionary is not found');


        $this->processingInfo = $this->dictionary->getProcessingInfo();
        $furtherMethod = "";



            switch($action){
                case "start":
                case "resume":
                    try {
                        $processingInfo = $this->em->find(DictionaryProcessing::class, $this->processingInfo->getId(), LockMode::PESSIMISTIC_WRITE);
                        if($processingInfo->getStatus()==DictionaryProcessing::STATUS_PENDING
                           || $processingInfo->getStatus()==DictionaryProcessing::STATUS_PAUSED) {
                            $furtherMethod = "startDictionaryProcessing";
                            $this->processingInfo->setStatus(DictionaryProcessing::STATUS_PROCESSING);
                        }
                        else{
                            $furtherMethod = "showDictionaryProcessing";
                        }
                        $this->em->flush($processingInfo);
                    }catch(PessimisticLockException $e){
                        $furtherMethod = "showDictionaryProcessing";
                    }
                    break;

                case "status":
                    $furtherMethod = "showDictionaryProcessing";
                    break;
                case "pause":
                    $furtherMethod = "pauseDictionaryProcessing";
                    break;
            }


        if($furtherMethod){
            return call_user_func([ $this, $furtherMethod]);
        }
        return new Response("unsupported dictionary processing action");

    }

    function listenToWordHandlerEvents(AbortableEvent $event){
        $processingRepository = $this->em->getRepository(DictionaryProcessing::class);
        $processingId = $this->processingInfo->getId();
        $status = $processingRepository->getStatus($processingId);
        if ($status == DictionaryProcessing::STATUS_PAUSING) {
            $event->abort();
        }
    }
    private function startDictionaryProcessing(){

        ini_set('max_execution_time', "0");
        $processingRepository = $this->em->getRepository(DictionaryProcessing::class);
        $processingId = $this->processingInfo->getId();
        $dictionary = $this->dictionary;
        $words = $dictionary->getWords();
        $wordHandler = $this->get('app.word_handler');


        $this->get('event_dispatcher')->addListener(TryLoadEvent::NAME, [ $this, 'listenToWordHandlerEvents']);
        $this->get('event_dispatcher')->addListener(WaitingEvent::NAME, [ $this, 'listenToWordHandlerEvents']);


        for($i=0; $i< $words->count(); $i++){

            $word = $words[$i];
            try {

                $wordHandler->handleWordAttributes($word);
                $processingRepository->updateProcessed($processingId, $i + 1);
            }
            catch(AbortedException $ex){
                $processingRepository->setStatus($processingId, DictionaryProcessing::STATUS_PAUSED);
                return new Response('paused!');
            }
            catch(\Exception $ex){
                $processingRepository->setStatus($processingId, DictionaryProcessing::STATUS_PAUSED);
                $message = $ex->getMessage(); // dev environment
                return new Response($message);
            }

        }

        //$processingRepository->updateProcessed($processingId, $words->count());
        $processingRepository->setStatus($processingId, DictionaryProcessing::STATUS_DONE);
        return new Response('done111!');
    }
    private function showDictionaryProcessing(){
        return new JsonResponse(array(
            'processed'=> $this->processingInfo->getProcessed(),
            'total'=> $this->processingInfo->getTotal(),
            'status'=> $this->processingInfo->getStatus()
        ));
    }
    private function pauseDictionaryProcessing(){
        $processingRepository = $this->em->getRepository(DictionaryProcessing::class);
        $processingId = $this->processingInfo->getId();
        $status = $processingRepository->getStatus($processingId);
        if($status == DictionaryProcessing::STATUS_PROCESSING) {
            $processingRepository->setStatus($processingId, DictionaryProcessing::STATUS_PAUSING);
            return new Response('pausing...');
        }
        return new Response('pausing is not allowed now');
    }
}