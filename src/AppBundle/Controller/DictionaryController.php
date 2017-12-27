<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 09.12.2017
 * Time: 17:19
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DictionaryController extends Controller {

    /**
     * @Route("/dictionary/list", name="dictionary_list")
     */
    public function listAction(Request $request){

        $dictionaries = $this->getDoctrine()->getRepository('AppBundle:Dictionary')->findAll();
        return $this->render('dictionary/list.html.twig', array(
            'dictionaries' => $dictionaries
        ));
    }

    /**
     * @param $id dictionary id
     * @Route("/dictionary/{id}/words", name="dictionary_words")
     */
    public function wordsAction($id){
        return $this->render('dictionary/words.html.twig');
    }
} 