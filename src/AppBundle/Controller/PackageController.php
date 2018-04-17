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
use AppBundle\Form\PackageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Dictionary;
use AppBundle\Entity\Package;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorBuilder;


/**
 * Class PackageController
 * @package AppBundle\Controller
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class PackageController extends Controller
{

    public function getRepository($class = Package::class)
    {
        return $this->getDoctrine()->getRepository($class);
    }

    public function getPackage($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param Request $request
     * @param $id
     * @param $wordId
     * @Route("/package/new", name="package_new")
     * @Method({"POST"})
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(PackageType::class);
        $form->handleRequest($request);
        $validator = $this->get('validator');
        //(new ValidatorBuilder())->getValidator()->validate('a')->get(0)->;
        $violationList = $validator->validate($form->get('name')->getData(), new NotBlank);

        if ($form->isSubmitted() && !$violationList->count()) {
            $dictionary = null;
            $dictionary_id = $form->get('dictionary_id')->getData();
            $isLocal = $form->get('is_local')->getData() && !is_null($dictionary_id);
            if ($isLocal) {
                $dictionary = $this->getRepository(Dictionary::class)->find($dictionary_id);
            }

            $package = new Package();
            $package->setName($form->get('name')->getData());
            $package->setDictionary($dictionary);
            $package->setLocal($isLocal);
            $package->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($package);
            $em->flush($package);
            return new JsonResponse(array(
                'id' => $package->getId()
            ));
        }
        $errors = array();
        foreach ($violationList as $violation) $errors['name'] = $violation->getMessage();
        return new JsonResponse(array(
            'errors' => $errors
        ), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Request $request
     * @param $id
     * @param $wordId
     * @Route("/package/update-word-entrance", name="package_update")
     * @Method({"POST"})
     */
    public function updateAction(Request $request)
    {
        $packageId = $request->request->get('packageId');
        $wordId = $request->request->get('wordId');
        $entrance = $request->request->get('entrance');

        if (is_null($packageId) or is_null($wordId) or is_null($entrance)) {
            return new JsonResponse(array(
                'error' => 'malformed request'
            ), Response::HTTP_BAD_REQUEST);
        }

        $package = $this->getRepository(Package::class)->find($packageId);
        $word = $this->getRepository(Word::class)->find($wordId);
        $entrance = $entrance == 'true'; // json
        if (is_null($package) or is_null($word)) {
            return new JsonResponse(array(
                'error' => 'word or package does not exist'
            ), Response::HTTP_BAD_REQUEST);
        }

        if ($entrance) {
            $package->addWord($word);
        } else {
            $package->removeWord($word);
        }
        $this->getDoctrine()->getManager()->flush();
        //
        return new JsonResponse(array(
            'result' => $entrance ? 'word attached' : 'word detached'
        ));
    }

    /**
     * @param Request $request
     * @param $id
     * @Route("/package/remove/{id}", name="package_remove")
     * @Method({"POST"})
     */
    public function removeAction(Request $request, $id)
    {
        $package = $this->getPackage($id);
        $user = $this->getUser();
        if($this->isGranted('ROLE_ADMIN') or $user->getId() == $package->getUser()->getId()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($package);
            $em->flush();
            return new JsonResponse(array(
                'status' => 'success',
                'removed package id' => $id
            ), Response::HTTP_OK);
        }
        return new JsonResponse(array(
            'status' => 'error',
            'message'=> array(
                'you are not allowed to remove this package'
            )
        ), Response::HTTP_FORBIDDEN);
    }
}