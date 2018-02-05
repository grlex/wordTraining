<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 06.09.2017
 * Time: 10:29
 */
namespace AppBundle\Service;

use AppBundle\Controller\DictionaryController;
use AppBundle\Entity\Settings;
use Symfony\Bridge\Doctrine\RegistryInterface;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
class BackgroundSubscriber implements EventSubscriberInterface {

    private $settingsRepository;
    public function __construct(RegistryInterface $doctrine){
        $this->settingsRepository = $doctrine->getRepository(Settings::class);
    }

    public function onKernelController(FilterControllerEvent $event){

        $request = $event->getRequest();
        if($request->isXmlHttpRequest() or $request->attributes->has('exception')) return;


        $setting = $this->settingsRepository->findOneBySetting(Settings::SETTING_SHOW_BACKGROUND_IMAGES);
        $showBackgrounds = $setting && $setting->getValue() ? true : false;

        if(!$showBackgrounds) return;


        $settings = $this->settingsRepository->findBySetting(Settings::SETTING_BACKGROUND_IMAGES);
        $backgroundsCount = count($settings);
        if($backgroundsCount==0) return;




        $session = $request->getSession();
        $index = $session->get('app.background-index', 0);

        for($i=0; $i<$backgroundsCount; $i++) {
            $index = ($index+1) % $backgroundsCount;
            list($inactive, $repeated, $filename) = explode('|', $settings[$index]->getValue());
            if($inactive=='1') continue;
            $backgroundURI = '/images/backgrounds/orig.'.$filename ;
            $request->attributes->set('app.background_uri', $backgroundURI);
            $request->attributes->set('app.background_repeated', $repeated);
            $session->set('app.background-index', $index);
            return;
        }




    }
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController')
        );
    }
}