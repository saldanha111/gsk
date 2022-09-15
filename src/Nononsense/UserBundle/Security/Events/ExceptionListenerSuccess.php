<?php

namespace Nononsense\UserBundle\Security\Events;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManager;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
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
    	$request = $this->requestStack->getCurrentRequest();
    	$logType = $this->em->getRepository(LogsTypes::class)->findOneBy(['stringId' => 'ACCESS']); //Access Type

        $log = new Logs();
        $log->setType($logType);
    	$log->setDate(new \DateTime());
        $log->setUser($event->getAuthenticationToken()->getUser());
    	$log->setDescription('Inicio de sesión correcto. Usuario: '.$event->getAuthenticationToken()->getUsername());
        $log->setIp($request->getClientIp());

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

}