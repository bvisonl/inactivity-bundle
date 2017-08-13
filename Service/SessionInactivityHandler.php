<?php

namespace Bvisonl\InactivityBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

class SessionInactivityHandler
{
    protected $container;
    protected $session;
    protected $router;
    protected $idleTimeout;
    protected $logoutUrl;

    public function __construct(ContainerInterface $container, RouterInterface $router)
    {
        $this->container = $container;
        $this->session = $container->get('session');
        $this->router = $router;
        $this->idleTimeout = ($container->hasParameter('bvisonl_inactivity_session_lifetime')) ? $container->getParameter('bvisonl_session_lifetime') : 300;
        $this->logoutUrl = ($container->hasParameter('bvisonl_inactivity_logout_route')) ? $container->getParameter('bvisonl_inactivity_logout_route') : "/";
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        // Must be logged in for this to actually do something and to avoid redirects
        if($this->getUser() == null) {
            return;
        }

        if ($this->idleTimeout > 0) {
            $idleTime = time() - $this->session->getMetadataBag()->getLastUsed();
            if ($idleTime > $this->idleTimeout) {
                if($this->container->hasParameter("bvisonl_inactivity_set_flash")) {
                    $type = ($this->container->hasParameter("bvisonl_inactivity_set_flash_type")) ? $this->container->getParameter("bvisonl_inactivity_set_flash_type") : "info";
                    $message = ($this->container->hasParameter("bvisonl_inactivity_set_flash_message")) ? $this->container->getParameter("bvisonl_inactivity_set_flash_message") : "You have been logged out due to inactivity.";
                    $this->session->getFlashBag()->set($type, $message);
                }
                $this->container->get('security.token_storage')->setToken(null);
                $url = ($this->logoutUrl != "/") ? $this->router->generate($this->logoutUrl) : $this->logoutUrl;
                $event->setResponse(new RedirectResponse($url));
            }
        }
    }

    /**
     * @return User|null
     */
    public function getUser()
    {

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            // no authentication information is available
            return null;
        }

        /** @var User $user */
        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}