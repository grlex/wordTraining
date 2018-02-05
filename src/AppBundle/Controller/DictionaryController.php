<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 09.12.2017
 * Time: 17:19
 */

namespace AppBundle\Controller;



use AppBundle\Entity\Word;
use AppBundle\Form\FilterType;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Dictionary;
use AppBundle\Entity\Package;
use AppBundle\Form\PackageType;

class DictionaryController extends Controller {

    const EXERCISE_CHOICE_TRANSLATION_FROM_SPELLING = 1;
    const EXERCISE_CHOICE_SPELLING_FROM_TRANSLATION = 2;
    const EXERCISE_CHOICE_SPELLING_FROM_PRONOUNCE = 3;
    const EXERCISE_CHOICE_TRANSLATION_FROM_PRONOUNCE = 4;
    const EXERCISE_TYPING_SPELLING_FROM_TRANSLATION = 5;
    const EXERCISE_TYPING_SPELLING_FROM_PRONOUNCE = 6;


    public function getRepository($class=Dictionary::class){
        return $this->getDoctrine()->getRepository($class);
    }
    public function getDictionary($id){
        return $this->getRepository()->find($id);
    }
    /**
     * @Route("/dictionary/list", name="dictionary_list")
     */
    public function listAction(Request $request){
        $filterFormHandler = $this->get('app.filter_form_handler');
        $filterForm = $filterFormHandler->createForm(array(
            'filterField' => 'name',
            'filterValue' => ''
        ));
        $filterFormHandler->handleRequest($filterForm, $request);

        $listQuery = $this->getRepository()->createListQuery();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $listQuery,
            $request->query->getInt('page', 1),
            10
        );


        return $this->render('dictionary/list.html.twig', array(
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView()
        ));
    }


    private function getUserPackages(Request $request, $id){
        $dictionary = $this->getDictionary($id);
        $chosenPackageId = $request->query->get('package_id', 'all-local');
        $chosenPackage = null;
        $userLocalPackages = array();
        $userGlobalPackages = array();
        if($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $userPackages = $this->getRepository(Package::class)->findByUser($this->getUser());
            foreach($userPackages as $package){
                if($package->isGlobal()) {
                    array_push($userGlobalPackages, $package);
                }
                else if($package->getDictionary()->getId() == $dictionary->getId()) {
                    array_push($userLocalPackages, $package);
                }
                if($chosenPackageId == $package->getId()){
                    $chosenPackage = $package;
                }
            }
        }
        if(is_null($chosenPackage)) {
            switch($chosenPackageId){
                case 'all-global':
                    $chosenPackage = new Package('all-global');
                    break;
                default:
                    $chosenPackage = new Package('all-local');
                    $chosenPackage->setDictionary($dictionary); // if all-local
                    break;
            }
        }
        return array(
            'locals' => $userLocalPackages,
            'globals' => $userGlobalPackages,
            'chosen' => $chosenPackage
        );
    }

    private function getPagination(Query $target, Request $request){
        $pageSize = $request->query->get('pageSize');
        if($pageSize=='all') {
            $target = $target->getResult();
            $pageSize = count($target);
        }
        else if((int)$pageSize) ;
        else $pageSize=10;

        $paginator = $this->get('knp_paginator');
        return $paginator->paginate(
            $target,
            $request->query->getInt('page', 1),
            $pageSize
            /*array(
                'filterFieldParameterName' => 'filterField',
                'filterValueParameterName' => 'filterValue',
                'pageParameterName' => 'page',
            )*/
        );


    }

    /**
     * @param Request $request
     * @param $id
     * @Route("/dictionary/{id}/words", name="dictionary_words")
     */
    public function wordsAction(Request $request, $id){
        $filterFormHandler = $this->get('app.filter_form_handler');
        $filterForm = $filterFormHandler->createForm(array(
            'filterField' => 'spelling.text',
            'filterValue' => ''
        ));
        $filterFormHandler->handleRequest($filterForm, $request);
        $newPackageForm = $this->createForm(PackageType::class, array(
            'dictionary_id' => $id
        ));
        $userPackages = $this->getUserPackages($request, $id);

        $wordListQuery = $this->getRepository()->createWordListQuery($id);

        return $this->render('dictionary/words.html.twig', array(
            'dictionary' => $this->getDictionary($id),
            'wordsFilterForm' => $filterForm->createView(),
            'wordsPagination' => $this->getPagination($wordListQuery, $request),
            'newPackageForm' => $newPackageForm->createView(),
            'userLocalPackages' => $userPackages['locals'],
            'userGlobalPackages' => $userPackages['globals'],
            'chosenPackage' => $userPackages['chosen']
        ));
    }

    /**
     * @param Request $request
     * @param $id
     * @param $wordId
     * @Route("/dictionary/{id}/exercise/{exercise}", name="dictionary_exercise")
     */
    public function exerciseAction(Request $request, $id, $exercise){
        $dictionary = $this->getDictionary($id);
        $newPackageForm = $this->createForm(PackageType::class, array(
            'dictionary_id' => $id
        ));
        $userPackages = $this->getUserPackages($request, $id);
        $chosenPackage = $userPackages['chosen'];

        if($chosenPackage->isPredefined()) {
            $dictionaryId = null;
            if ($chosenPackage->isLocal()) {
                $dictionaryId = $chosenPackage->getDictionary()->getId();
            }
            $words = $this->getRepository(Dictionary::class)->createWordListQuery($dictionaryId)->getResult();
            $chosenPackage->setWords($words);
        }


        return $this->render('dictionary/exercise.html.twig', array(
            'exercise' => $exercise,
            'dictionary' => $dictionary,
            'newPackageForm' => $newPackageForm->createView(),
            'userLocalPackages' => $userPackages['locals'],
            'userGlobalPackages' => $userPackages['globals'],
            'chosenPackage' => $userPackages['chosen']
        ));
    }
} 