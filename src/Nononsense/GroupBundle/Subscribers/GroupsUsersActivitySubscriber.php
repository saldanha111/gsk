<?php
declare(strict_types=1);

namespace Nononsense\GroupBundle\Subscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\UserBundle\Entity\Users;
use Symfony\Component\HttpFoundation\RequestStack;

class GroupsUsersActivitySubscriber implements EventSubscriber
{
    const PERSIST = "persist";
    const REMOVE = "remove";

    /**
     * @var EntityManagerInterface
     */
    private $context;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct($context,  RequestStack $requestStack)
    {
        $this->context = $context;
        $this->requestStack = $requestStack;
    }
    
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postRemove,
        ];
    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->logActivity(self::PERSIST, $args);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->logActivity(self::REMOVE, $args);
    }

    private function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        $request = $this->requestStack->getCurrentRequest();

        // if this subscriber only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if ($entity instanceof GroupUsers) {
            /** @var LogsTypes $logType */
            $logType = $args->getEntityManager()->getRepository(LogsTypes::class)->findOneBy(['id' => 2]); //Adding or removing user into a group
            /** @var Users $userLogged */
            $userLogged = $this->context->getToken()->getUser();
            $description = '';
            $affectedUser = $entity->getUser()->getName();
            $groupName = $entity->getGroup()->getName();
            $groupId = $entity->getGroup()->getId();
            $ip = $request->getClientIp();
            switch($action) {
                case self::PERSIST:
                    $description = "Usuario añadido: " . $affectedUser . " al grupo: " . $groupName ."(" . $groupId . ")  IP:" . $ip;
                    break;
                case self::REMOVE:
                    $description = "Usuario eliminado: " . $affectedUser . " del grupo: " . $groupName ."(" . $groupId . ") IP:" . $ip;
                    break;
                default:
                    break;
            }

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