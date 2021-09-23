<?php

namespace Nononsense\HomeBundle\Utils;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Nononsense\HomeBundle\Entity\Logs;
use Nononsense\HomeBundle\Entity\LogsTypes;
use Nononsense\HomeBundle\Entity\LogsTypesRepository;
use Nononsense\HomeBundle\Entity\Tokens;
use Nononsense\UserBundle\Entity\Users;

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
        if($user && $user !== 'anon.'){
            $token_get_data->setUser($user);
        }

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

    public function tokenRemove($token){
        $expired_token = 0;
        $tokenObj = $this->em->getRepository('NononsenseHomeBundle:Tokens')->findOneByToken($token);
        if($tokenObj){
            $this->em->remove($tokenObj);
            $this->em->flush();
        }
        return TRUE;
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

    public function sendNotification($mailTo, $link, $logo, $accion, $subject, $message, $useTemplate = true)
    {
        if($useTemplate){
            $renderedBody = $this->templating->render(
                'NononsenseHomeBundle:Email:notificationUser.html.twig', array(
                'logo' => $logo,
                'accion' => $accion,
                'message' => $message,
                'link' => $link
            ));
        }else{
            $renderedBody = $message;
        }
        $email = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->container->getParameter('mailer_username'))
            ->setTo($mailTo)
            ->setBody($renderedBody,'text/html');
        if ($this->container->get('mailer')->send($email)) {
            //echo '[SWIFTMAILER] sent email to ' . $mailTo;
            //echo 'LOG: ' . $mailLogger->dump();
            return true;
        } else {
            //echo '[SWIFTMAILER] not sending email: ' . $mailLogger->dump();
            return false;
        }

    }

    public function signWithP12($path_document_to_sign, $p12Path, $p12Pass){
        try {
            $command = 'AutoFirma sign -i '.$path_document_to_sign.' -o '.$path_document_to_sign.' -store pkcs12:'.$p12Path.' -filter cualquiertexto -password '.$p12Pass;
            $result = shell_exec($command);
            if(strpos($result,'La operacion ha terminado correctamente') === false){
                return false;
            }
        } catch(\Exception $ex){
            $this->logger->error("Utilities->signWithP12: ".$ex->getCode().": ".$ex->getMessage());
            return false;
        }

        return true;
    }

    public function saveLog(string $type, string $description)
    {
        /** @var LogsTypesRepository $logsTypesRepository */
        $logsTypesRepository = $this->em->getRepository(LogsTypes::class);
        /** @var LogsTypes $logType */
        $logType = $logsTypesRepository->findOneBy(['stringId' => $type]);
        if(!$logType){
            $logType = $logsTypesRepository->findOneBy(['stringId' => 'unknown']);
        }

        $log = new Logs();
        $log->setType($logType);
        $log->setDate(new DAteTime());
        $log->setDescription($description);
        $this->em->persist($log);
        try {
            $this->em->flush();
        } catch (OptimisticLockException $e) {
        }
    }

    public function checkUser($password, $username=''){

        if (!$username) $username = $this->container->get('security.context')->getToken()->getUsername();
        $password       = $password;

        $user           = $this->em->getRepository('NononsenseUserBundle:Users')->findOneBy(array('username' => $username));

        if ($user) {
            
            $factory    = $this->container->get('security.encoder_factory');
            $encoder    = $factory->getEncoder($user);

            $validPassword = ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) ? true : false;
            return $validPassword;
        }

        return false;   
    }
}
