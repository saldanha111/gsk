<?php

namespace Nononsense\UserBundle\Security\Events;

use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Doctrine\ORM\EntityManager;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\UserBundle\Entity\Users;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExceptionListenerFail
{
	function __construct(EntityManager $entityManager, RequestStack $requestStack,  ValidatorInterface $validator)
	{
		$this->em = $entityManager;
		$this->requestStack = $requestStack;
        $this->validator = $validator;
	}

    public function onAuthenticationFailureLogin(AuthenticationFailureEvent $event)
    {
    	$request = $this->requestStack->getCurrentRequest();
    	$logType = $this->em->getRepository(LogsTypes::class)->findOneBy(['stringId' => 'ACCESS']); //Access Type
        $username = $event->getAuthenticationToken()->getUsername();

        $isEmail = $this->validator->validate(
            $username,
            new Assert\Email()
        );

        $filter = (0 === count($isEmail)) ? ['email' => $username] : ['username' => $username];
        $user = $this->em->getRepository(Users::class)->findOneBy($filter);

        $log = new Logs();
        $log->setType($logType);
    	$log->setDate(new \DateTime());
        $log->setUser($user);
    	$log->setDescription('Inicio de sesión fallido. Usuario: '.$event->getAuthenticationToken()->getUsername());
        $log->setIp($request->getClientIp());

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }
}