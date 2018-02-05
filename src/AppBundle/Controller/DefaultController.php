<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Templating\EngineInterface;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute("dictionary_list");
    }
    /**
     * @Route("/change-locale/{_locale}", name="change_locale", requirements={"_locale":"ru|en"})
     * @Method({"GET"})
     */
    public function changeLocale(Request $request){
        $backUri = $request->query->get('back-uri', '/');
        return $this->redirect($backUri);
    }

}
