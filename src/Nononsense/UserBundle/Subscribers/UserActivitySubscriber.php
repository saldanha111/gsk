<?php
declare(strict_types=1);

namespace Nononsense\UserBundle\Subscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\UserBundle\Entity\Users;
use Symfony\Component\HttpFoundation\RequestStack;

class UserActivitySubscriber implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $context;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct($context, RequestStack $requestStack)
    {
        $this->context = $context;
        $this->requestStack = $requestStack;
    }
    
    public function getSubscribedEvents()
    {
        return [
            Events::postUpdate
        ];
    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->logActivity($args);
    }

    private function logActivity(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        $request = $this->requestStack->getCurrentRequest();

        if ($entity instanceof Users) {

            $description = "Usuario: " . $entity->getName() . " ha sido editado.";
            $ip = $request->getClientIp();

            /** @var LogsTypes $logType */
            $logType = $args->getEntityManager()->getRepository(LogsTypes::class)->findOneBy(['id' => 1]); //Adding or removing user into a group
            /** @var Users $userLogged */
            $userLogged = $this->context->getToken()->getUser();
            $log = new Logs();
            $log->setType($logType);
            $log->setDate(new \DateTime());
            $log->setUser($userLogged);
            $log->setDescription($description);

            $args->getEntityManager()->persist($log);
            $args->getEntityManager()->flush();
        }
    }
}