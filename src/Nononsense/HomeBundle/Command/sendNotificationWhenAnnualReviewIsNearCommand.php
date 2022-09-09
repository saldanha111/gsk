<?php
declare(strict_types=1);

namespace Nononsense\HomeBundle\Command;

use Doctrine\ORM\EntityManager;
use Nononsense\HomeBundle\Entity\Areas;
use Nononsense\HomeBundle\Entity\TMTemplates;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class sendNotificationWhenAnnualReviewIsNearCommand
{

    protected function configure()
    {
        $this->setName('gsk:sendNotificationWhenAnnualReviewIsNearCommand')->setDescription('Send email when annual review is near');
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