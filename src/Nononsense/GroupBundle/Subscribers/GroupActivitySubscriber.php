<?php
declare(strict_types=1);

namespace Nononsense\GroupBundle\Subscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\UserBundle\Entity\Users;
use Symfony\Component\HttpFoundation\RequestStack;

class GroupActivitySubscriber implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $context;
    /**
     * @var RequestStack
     */
    private $requestStack;
    private $translator;

    public function __construct($context,  $translator, RequestStack $requestStack)
    {
        $this->context = $context;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
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

        if ($entity instanceof Groups) {

            $description = $this->translator->trans('The group named: "') . $entity->getName() . $this->translator->trans('" has been edited.');
            $groupName = $entity->getName();
            $groupId = $entity->getId();
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