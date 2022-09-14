<?php

namespace Nononsense\UserBundle\Security\Events;

use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Doctrine\ORM\EntityManager;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Symfony\Component\HttpFoundation\RequestStack;

class ExceptionListenerFail
{
	function __construct(EntityManager $entityManager, RequestStack $requestStack)
	{
		$this->em = $entityManager;
		$this->requestStack = $requestStack;
	}

    public function onAuthenticationFailureLogin(AuthenticationFailureEvent $event)
    {
    	$request = $this->requestStack->getCurrentRequest();
    	$logType = $this->em->getRepository(LogsTypes::class)->findOneBy(['id' => 1]); //Access Type
        
        $log = new Logs();
        $log->setType($logType);
    	$log->setDate(new \DateTime());
    	$log->setDescription('Inicio de sesión fallido. Usuario: '.$event->getAuthenticationToken()->getUsername());
        $log->setIp($request->getClientIp());

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }
}