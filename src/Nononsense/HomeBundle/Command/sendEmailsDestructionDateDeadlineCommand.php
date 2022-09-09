<?php

namespace Nononsense\HomeBundle\Command;

use Doctrine\ORM\EntityManager;
use Exception;
use http\Client\Curl\User;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\Products;
use Nononsense\HomeBundle\Entity\ProductsInputs;
use Nononsense\HomeBundle\Entity\ProductsInputStatus;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Nononsense\HomeBundle\Services\TMTemplatesService;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class sendEmailsDestructionDateDeadlineCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gsk:sendEmailsDestructionDateDeadline')->setDescription('Send email when destruction date of any template is six month less than today');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        try {
            $templates = $em->getRepository(TMTemplates::class)->findTemplatesWithDestructionDateBeforeThisInterval(6 . "M");
            $this->sendAlert($em, $templates, $container);
        } catch(Exception $exception) {
            $output->writeln("Lo sentimos, hubo un problema cuando buscábamos las plantillas para enviarlas.");
        }
        $output->writeln("fin");
    }

    private function sendAlert($em, $templates, $container)
    {
        $emails = []; $data = [];
        /** @var TMTemplates $template */
        foreach ($templates as $template) {
            $data[] = $template;
            /** @var Areas $area */
            $areaId = $template["area"];
            $area = $this->getDoctrine()->getManager()->getRepository(Areas::class)->find($areaId);
            $areaUser = $area->getFll();
            if(!is_null($areaUser)) {
                $emails[] = $areaUser->getEmail();
            }
        }

        if (!count($emails) == 0) {
            $message = \Swift_Message::newInstance()
                    ->setSubject('Notificación')
                    ->setFrom($container->getParameter('mailer_username'))
                    ->setTo($emails)
                    ->setBody(
                        $container->get('templating')->render(
                            'NononsenseHomeBundle:Email:notificationDestructionDate.html.twig', [
                                "templates" => $data
                            ]
                        )
                    )
            ;
            $message->setContentType("text/html");
            $container->get('mailer')->send($message);
        }
    }
}
