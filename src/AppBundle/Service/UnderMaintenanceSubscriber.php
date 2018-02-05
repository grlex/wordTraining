<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 06.09.2017
 * Time: 10:29
 */
namespace AppBundle\Service;

use AppBundle\Entity\Settings;
use Symfony\Bridge\Doctrine\RegistryInterface;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

class UnderMaintenanceSubscriber implements EventSubscriberInterface {

    private $settingsRepository;
    private $templating;
    private $tokenStorage;
    public function __construct(RegistryInterface $doctrine, EngineInterface $templating, TokenStorageInterface $tokenStorage){
        $this->settingsRepository = $doctrine->getRepository(Settings::class);
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(GetResponseEvent $event){

        $setting = $this->settingsRepository->findOneBySetting(Settings::SETTING_UNDER_MAINTENANCE);
        $underMaintenance = $setting && $setting->getValue() ? true : false;

        if(!$underMaintenance) return;

        $token = $this->tokenStorage->getToken();
        if($token) {
            foreach ($token->getRoles() as $role) {
                if ($role->getRole() == 'ROLE_ADMIN') return;
            }
        }


        $content = $this->templating->render('default/under_maintenance.html.twig');
        $response = new Response($content);
        $response->send();
        die();
    }
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 0))
        );
    }
}