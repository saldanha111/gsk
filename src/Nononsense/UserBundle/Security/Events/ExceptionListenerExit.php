<?php

namespace Nononsense\UserBundle\Security\Events;

use Doctrine\ORM\EntityManager;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ExceptionListenerExit implements LogoutHandlerInterface
{
	function __construct(EntityManager $entityManager, RequestStack $requestStack)
	{
		$this->em = $entityManager;
		$this->requestStack = $requestStack;
	}

    public function logout(Request $request, Response $response, TokenInterface $authToken)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (is_object($authToken->getUser())) {
        	$logType = $this->em->getRepository(LogsTypes::class)->findOneBy(['stringId' => 'ACCESS']); //Access Type

	        $log = new Logs();
	        $log->setType($logType);
	        $log->setDate(new \DateTime());
	        $log->setUser($authToken->getUser());
	        $log->setDescription('Sesión finalizada. Usuario: '.$authToken->getUser()->getUsername());
	        $log->setIp($request->getClientIp());

	        $this->em->persist($log);
	        $this->em->flush();

	        return $log;
        }
    }
}