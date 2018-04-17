<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.12.2017
 * Time: 17:27
 */
namespace Custom\EasyAdmin\Controller;

use AppBundle\Entity\Settings;
use Custom\EasyAdmin\Form\SettingsBackgroundImageType;
use Custom\EasyAdmin\Form\SettingsType;
use AppBundle\Entity\Word;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Form\Util\LegacyFormHelper;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class AdminController extends BaseAdminController {

    protected function createNewUserEntity()
    {
        return $this->getUserManager()->createUser();
    }

    protected function prePersistUserEntity($entity)
    {
        $this->getUserManager()->updateUser($entity);
    }

    protected function preUpdateUserEntity($entity)
    {
        $this->getUserManager()->updateUser($entity, false);
    }

    protected function preRemoveUserEntity($entity)
    {
        $this->getUserManager()->deleteUser($entity);
    }

    /**
     * @return UserManager
     */
    private function getUserManager(){
        return $this->get('fos_user.user_manager');
    }



    public function createWordListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter){
        $qb = $this->em->createQueryBuilder()
            ->from(Word::class, 'entity')
            ->select('entity');
        switch($sortField){
            case 'spelling':
                $qb->leftJoin('entity.spelling', 'spelling')
                    ->orderBy('spelling.text', $sortDirection);
                break;
            case 'translation':
                $qb->leftJoin('entity.translation', 'translation')
                    ->orderBy('translation.text', $sortDirection);
                break;
            case 'transcription':
                $qb->leftJoin('entity.transcription', 'transcription')
                    ->orderBy('transcription.text', $sortDirection);
                break;
            case 'dictionary':
                $qb->leftJoin('entity.dictionary', 'dictionary')
                    ->orderBy('dictionary.name', $sortDirection);
        }
        return $qb;
    }

    /**
     * @param $term
     * @Route("/image-search/{term}")
     */
    public function imageSearchAction($term){
        $response = new JsonResponse();
        try {
            $images = $this->get('app.image_searcher')->search($term);
            $response->setData($images);
        }catch(\Exception $ex){
            $response->setStatusCode(500);
            $response->setData(array('result'=>$ex->getMessage()));
        }
        return $response;
    }



}

