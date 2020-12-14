<?php

namespace Nononsense\HomeBundle\Command;

use Doctrine\ORM\EntityManager;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\HomeBundle\Entity\Products;
use Nononsense\HomeBundle\Entity\ProductsInputs;
use Nononsense\HomeBundle\Entity\ProductsInputStatus;
use Nononsense\UserBundle\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class sendEmailsProductsAlertsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gsk:sendEmailsProductsAlerts')->setDescription('Send email alerts products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $hectorUsers = $this->getHectorUsers($em);

        $this->sendStockAlert($em, $hectorUsers, $container);
        $this->sendExpiredAlert($em, $hectorUsers, $container);
        $output->writeln("fin");
    }

    private function sendStockAlert($em, $adminUsers, $container)
    {
        $productsUnderMinStock = $this->getProductsUnderMinStock($em);
        $emails = [];
        if($productsUnderMinStock && count($adminUsers) > 0){
            foreach($adminUsers as $user){
                array_push($emails, $user->getEmail());
            }
            $message = \Swift_Message::newInstance()
                ->setSubject('Alerta stock productos por debajo del stock mínimo')
                ->setFrom($container->getParameter('mailer_username'))
                ->setTo($emails)
                ->setBody(
                    $container->get('templating')->render(
                        'NononsenseHomeBundle:Email:notificationProductsUnderStock.html.twig',
                        ['productsUnderMinStock' => $productsUnderMinStock]
                    )
                );
            $message->setContentType("text/html");
            $container->get('mailer')->send($message);
        }
    }

    private function sendExpiredAlert($em, $adminUsers, $container)
    {
        $expiredState = $em->getRepository(ProductsInputStatus::class)->findOneBy(['slug' => 'caducado']);
        $endState = $em->getRepository(ProductsInputStatus::class)->findOneBy(['slug' => 'terminado']);
        $productsExpired = $this->getProductsExpired($em, $expiredState, $endState);
        $emails = [];

        if($productsExpired && count($adminUsers) > 0){
            foreach($adminUsers as $user){
                array_push($emails, $user->getEmail());
            }
            $message = \Swift_Message::newInstance()
                ->setSubject('Alerta productos caducados o con fecha de destrucción')
                ->setFrom($container->getParameter('mailer_username'))
                ->setTo($emails)
                ->setBody(
                    $container->get('templating')->render(
                        'NononsenseHomeBundle:Email:notificationProductsDateExpired.html.twig',
                        ['productsExpired' => $productsExpired]
                    )
                );
            $message->setContentType("text/html");
            $container->get('mailer')->send($message);
            /** @var ProductsInputs $product */
            foreach($productsExpired as $product){
                $product->setState($expiredState);
                $em->persist($product);
            }
            $em->flush();
        }
    }

    /**
     * @param EntityManager
     * @return array
     */
    private function getAdminUsers($em): array
    {
        $adminGroup = $em->getRepository(Groups::class)->findOneBy(['name' => 'reactivos-admin']);
        if ($adminGroup) {
            $usersInGroup = $em->getRepository(Users::class)->findAllUsersInGroup((int) $adminGroup->getId());
        }
        return (isset($usersInGroup) && $usersInGroup) ? $usersInGroup : [];
    }

    /**
     * @param EntityManager
     * @return array
     */
    private function getHectorUsers($em): array
    {
        $adminGroup = $em->getRepository(Groups::class)->findOneBy(['name' => 'ARA_SUSTANCIAS CADUCADAS_HeCToR']);
        if ($adminGroup) {
            $usersInGroup = $em->getRepository(Users::class)->findAllUsersInGroup((int) $adminGroup->getId());
        }
        return (isset($usersInGroup) && $usersInGroup) ? $usersInGroup : [];
    }

    private function getProductsUnderMinStock($em)
    {
        return $em->getRepository(Products::class)->list(['underMinimumStock' => 1],0);
    }

    private function getProductsExpired($em, $expiredState, $endState)
    {
        return $em->getRepository(ProductsInputs::class)->list(['expired' => true, 'expiredState' => $expiredState->getId(), 'endState' => $endState->getId()],0);
    }
}
