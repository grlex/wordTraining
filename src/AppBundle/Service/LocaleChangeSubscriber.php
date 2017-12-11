<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 06.09.2017
 * Time: 10:29
 */
namespace AppBundle\Service;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
class LocaleChangeSubscriber implements EventSubscriberInterface {

    private $defaultLocale;
    public function __construct(RequestStack $requestStack){
        $request = $requestStack->getCurrentRequest();
        $this->defaultLocale = $request->getPreferredLanguage(['ru','en-EN']);

    }
    public function onKernelRequest(GetResponseEvent $event){


        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            $request->setLocale($this->defaultLocale);
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->setLocale($locale);
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest',15))
        );
    }
}