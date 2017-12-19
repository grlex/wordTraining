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
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;

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



    protected function loadDictionaryAction(){
        if($this->request->getSession()->isStarted()){
            $this->request->getSession()->save();
        }
        $dictionaryId = $this->request->query->get('id');


    }

    protected function createDictionaryEntityFormBuilder($dictionary, $view)
    {
        $formBuilder = parent::createEntityFormBuilder($dictionary, $view);
        $formBuilder->remove('words');
        $formBuilder->add('words', \Custom\EasyAdmin\Form\WordsCollectionType::class);
        return $formBuilder;
    }


    protected function prePersistDictionaryEntity($dictionary)
    {
        //$words = $dictionary->getWords();
        //foreach($words as $word) $word->addToDictionary($dictionary);
        $this->preSaveDictionaryEntity($dictionary);
    }

    protected function preUpdateDictionaryEntity($dictionary)
    {
        $this->preSaveDictionaryEntity($dictionary);
    }
    protected function preSaveDictionaryEntity(Dictionary $dictionary){

    }
}