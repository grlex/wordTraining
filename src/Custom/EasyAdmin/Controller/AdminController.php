<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 08.12.2017
 * Time: 17:27
 */
namespace Custom\EasyAdmin\Controller;

use AppBundle\Entity\Dictionary;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use FOS\UserBundle\Doctrine\UserManager;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends BaseAdminController {
    protected $persistedDictionary;

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


    protected function prePersistDictionaryEntity(Dictionary $dictionary){
        $this->persistedDictionary = $dictionary;
    }
    protected function preRemoveDictionaryEntity($dictionary){
        $loadProgressFile = sprintf('%s/var/dictionaryLoading/%d.json', $this->getParameter('kernel.project_dir'), $dictionary->getId());
        if(file_exists($loadProgressFile)){
            unlink($loadProgressFile);
        }
    }

}