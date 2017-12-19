<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute("dictionary_list");
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
    /**
     * @Route("/change-locale/{_locale}", name="change_locale", requirements={"_locale":"ru|en"})
     * @Method({"GET"})
     */
    public function changeLocale(Request $request){
        $backUri = $request->query->get('back-uri', '/');
        return $this->redirect($backUri);
    }

    /**
     * @Route("/delay-test/{seconds}")
     */
    public function delayTestAction(Request $request, $seconds=10){
        if($request->getSession()->isStarted())
            $request->getSession()->save();
        sleep($seconds);
        return new Response('delay test page');
    }
}
