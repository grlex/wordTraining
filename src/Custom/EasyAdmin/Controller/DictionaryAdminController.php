<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.12.2017
 * Time: 17:27
 */
namespace Custom\EasyAdmin\Controller;

use AppBundle\Entity\Dictionary;
use AppBundle\Entity\DictionaryLoading;
use AppBundle\Entity\Word;
use AppBundle\WordLoader\Exception\AbortedException;
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
use AppBundle\WordLoader\Event\TryLoadEvent;
use AppBundle\WordLoader\Event\WaitingEvent;

class DictionaryAdminController extends BaseAdminController {

    private $loadingInfo;
    private $dictionary;

    protected function prePersistDictionaryEntity($dictionary){
        $loadingInfo = new DictionaryLoading();
        $dictionary->setLoadingInfo($loadingInfo);
        $this->preSaveDictionaryEntity($dictionary);
    }

    protected function preUpdateDictionaryEntity($dictionary){
        $this->preSaveDictionaryEntity($dictionary);
    }

    protected function preSaveDictionaryEntity(Dictionary $dictionary){

        $words = $dictionary->getWords();

        foreach($words as &$word){
            try {
                $this->em->persist($word);
                $this->em->flush($word);
            }
            catch(\Exception $ex){

                $word = $this->em->getRepository(Word::class)->findBySpelling($word->getSpelling());
            }
        }
        $word = null;

        $loadingInfo = $dictionary->getLoadingInfo();
        $loadingInfo->setTotal($words->count())
            ->setLoaded(0)
            ->setStatus(DictionaryLoading::STATUS_PENDING);
    }

    /**
     * @param $id
     * @return mixed|Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @Route("/dictionary/load/{id}")
     * @Method({"POST", "GET"})
     */
    public  function loadDictionaryAction(Request $request, $id){
        $this->request = $request;
        $this->em = $this->getDoctrine()->getManager();
        if($this->request->getSession()->isStarted()){
            $this->request->getSession()->save();
        }
        $action = $this->request->query->get('action', 'status'); // start, status, pause, resume
        if($this->request->getMethod()!=Request::METHOD_POST) $action = 'status';

        $this->dictionary = $this->em->getRepository(Dictionary::class)
            ->findWithLoadingInfo($id);

        if(is_null($this->dictionary))
            return new Response('dictionary is not found');


        $this->loadingInfo = $this->dictionary->getLoadingInfo();
        $furtherMethod = "";



            switch($action){
                case "start":
                case "resume":
                    try {
                        $loadingInfo = $this->em->find(DictionaryLoading::class, $this->loadingInfo->getId(), LockMode::PESSIMISTIC_WRITE);
                        if($loadingInfo->getStatus()==DictionaryLoading::STATUS_PENDING
                           || $loadingInfo->getStatus()==DictionaryLoading::STATUS_PAUSED) {
                            $furtherMethod = "startDictionaryLoading";
                            $this->loadingInfo->setStatus(DictionaryLoading::STATUS_LOADING);
                        }
                        else{
                            $furtherMethod = "showDictionaryLoading";
                        }
                        $this->em->flush($loadingInfo);
                    }catch(PessimisticLockException $e){
                        $furtherMethod = "showDictionaryLoading";
                    }
                    break;

                case "status":
                    $furtherMethod = "showDictionaryLoading";
                    break;
                case "pause":
                    $furtherMethod = "pauseDictionaryLoading";
                    break;
            }


        if($furtherMethod){
            return call_user_func([ $this, $furtherMethod]);
        }
        return new Response("unsupported dictionary loading action");

    }

    function listenToWordLoaderEvents($event){
        $loadingRepository = $this->em->getRepository(DictionaryLoading::class);
        $loadingId = $this->loadingInfo->getId();
        $status = $loadingRepository->getStatus($loadingId);
        if ($status == DictionaryLoading::STATUS_PAUSING) {
            $event->abort();
        }
    }
    private function startDictionaryLoading(){

        $loadingRepository = $this->em->getRepository(DictionaryLoading::class);
        $loadingId = $this->loadingInfo->getId();
        $dictionary = $this->dictionary;
        $words = $dictionary->getWords();
        $wordLoader = $this->get('app.word_loader');


        $this->get('event_dispatcher')->addListener(TryLoadEvent::NAME, [ $this, 'listenToWordLoaderEvents']);
        $this->get('event_dispatcher')->addListener(WaitingEvent::NAME, [ $this, 'listenToWordLoaderEvents']);


        for($i=0; $i< $words->count(); $i++){

            $word = $words[$i];
            try {
                $wordLoader->loadWord($word);
                $loadingRepository->updateLoaded($loadingId, $i + 1);
            }
            catch(AbortedException $ex){
                // only if aborted
                $loadingRepository->setStatus($loadingId, DictionaryLoading::STATUS_PAUSED);
                return new Response('paused!');
            }
        }

        $loadingRepository->setStatus($loadingId, DictionaryLoading::STATUS_DONE);
        return new Response('done!');
    }
    private function showDictionaryLoading(){
        return new JsonResponse(array(
            'loaded'=> $this->loadingInfo->getLoaded(),
            'total'=> $this->loadingInfo->getTotal(),
            'status'=> $this->loadingInfo->getStatus()
        ));
    }
    private function pauseDictionaryLoading(){
        $loadingRepository = $this->em->getRepository(DictionaryLoading::class);
        $loadingId = $this->loadingInfo->getId();
        $status = $loadingRepository->getStatus($loadingId);
        if($status == DictionaryLoading::STATUS_LOADING) {
            $loadingRepository->setStatus($loadingId, DictionaryLoading::STATUS_PAUSING);
            return new Response('pausing...');
        }
        return new Response('pausing is not allowed now');
    }
}