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
use AppBundle\Entity\DictionaryLoadingRepository;
use AppBundle\Entity\Word;
use AppBundle\WordLoader\Exception\AbortedException;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\PessimisticLockException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use FOS\UserBundle\Doctrine\UserManager;
use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\WordLoader\Event\TryLoadEvent;
use AppBundle\WordLoader\Event\WaitingEvent;

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
}