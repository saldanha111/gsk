<?php

namespace Nononsense\HomeBundle\Utils;

use Nononsense\HomeBundle\Entity\Tokens;

class Utilities{
    
    public function __construct(\Doctrine\ORM\EntityManager $em, $logger, $session, $container, $templating) {
        $this->em = $em;
        $this->logger = $logger;
        $this->session = $session;
        $this->container = $container;
        $this->templating = $templating;
    }

    public function generateToken()
    {
    	$user = $this->container->get('security.context')->getToken()->getUser();

    	$token = uniqid().rand(1000,9999);
        $token_get_data = new Tokens();
        $token_get_data->setToken($token);
        $token_get_data->setUser($user);

        $this->em->persist($token_get_data);
        $this->em->flush();

        return $token;
    }

    public function tokenExpired($token){
    	$expired_token = 0;
        $tokenObj = $this->em->getRepository('NononsenseHomeBundle:Tokens')->findOneByToken($token);
        if($tokenObj){
            $token_date_created = $tokenObj->getCreated();
            $token_date_created->modify('+15 minute');
            $current_minute = date('YmdHis');
            if($current_minute > $token_date_created->format('YmdHis')){
                $expired_token = 1;
            }
            $this->em->persist($tokenObj);
            $this->em->flush();
        }

        return $expired_token;
    }

    public function getUserByToken($token){
        $expired_token = 0;
        $tokenObj = $this->em->getRepository('NononsenseHomeBundle:Tokens')->findOneByToken($token);
        if($tokenObj){
            $token_date_created = $tokenObj->getCreated();
            $token_date_created->modify('+15 minute');
            $current_minute = date('YmdHis');
            if($current_minute < $token_date_created->format('YmdHis')){
                $this->em->persist($tokenObj);
                $this->em->flush();
                return $tokenObj->getUser()->getId();
            }
            return false;
        }

        return false;
    }

    public function sendNotification($mailTo, $link, $logo, $accion, $subject, $message)
    {
        $email = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo($mailTo)
            ->setBody(
                $this->templating->render(
                    'NononsenseHomeBundle:Email:notificationUser.html.twig', array(
                    'logo' => $logo,
                    'accion' => $accion,
                    'message' => $message,
                    'link' => $link
                )),
                'text/html'
            );
        if ($this->container->get('mailer')->send($email)) {
            //echo '[SWIFTMAILER] sent email to ' . $mailTo;
            //echo 'LOG: ' . $mailLogger->dump();
            return true;
        } else {
            //echo '[SWIFTMAILER] not sending email: ' . $mailLogger->dump();
            return false;
        }

    }
}
