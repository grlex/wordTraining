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
use Proxies\__CG__\AppBundle\Entity\DictionaryGroup;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DictionaryGroupAdminController extends BaseAdminController {

    private $parentGroupId;

    public function newChildAction(){
        $this->parentGroupId = $this->request->query->get('id',null);
        return $this->newAction();
    }

    public function createNewDictionaryGroupEntity(){
        $entity = new DictionaryGroup();
        if(!is_null($this->parentGroupId)){
            $parentGroup = $this->em->getRepository(DictionaryGroup::class)->find($this->parentGroupId);
            $entity->setParent($parentGroup);
        }
        return $entity;
    }

    public function prePersistDictionaryGroupEntity(DictionaryGroup $entity){
        if(is_null($entity->getSort())){
            $this->em->persist($entity);
            $this->em->flush();
            $this->em->createQuery('update AppBundle\\Entity\\DictionaryGroup g set g.sort=:id where g.id = :id')
                ->setParameter(':id', $entity->getId())
                ->execute();
        }
    }

    public function reorderAction(){
        $groupRepository = $this->em->getRepository(DictionaryGroup::class);
        $firstId = $this->request->get('group1');
        $secondId = $this->request->get('group2');
        $firstGroup = $groupRepository->find($firstId);
        $secondGroup = $groupRepository->find($secondId);
        $tmpSort = $firstGroup->getSort();
        $firstGroup->setSort($secondGroup->getSort());
        $secondGroup->setSort($tmpSort);
        $this->em->flush();
        return new JsonResponse([
            $firstGroup->getTitle() => $firstGroup->getSort(),
            $secondGroup->getTitle() => $secondGroup->getSort()
        ]);
    }



}

