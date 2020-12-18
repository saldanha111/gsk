<?php

namespace Nononsense\UserBundle\Security\Events;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManager;
use Nononsense\HomeBundle\Entity\Logs;
use Symfony\Component\HttpFoundation\RequestStack;

class ExceptionListenerSuccess
{

	function __construct(EntityManager $entityManager, RequestStack $requestStack)
	{
		$this->em = $entityManager;
		$this->requestStack = $requestStack;
	}

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
    	$log = new Logs();

    	$request = $this->requestStack->getCurrentRequest();
    	
    	$log->setDate(new \DateTime());
    	$log->setDescription('Inicio de sesión aceptado. Usuario: '.$event->getAuthenticationToken()->getUsername().' IP:'.$request->getClientIp());

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

}