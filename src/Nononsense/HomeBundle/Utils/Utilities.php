<?php

namespace Nononsense\HomeBundle\Utils;

use Nononsense\HomeBundle\Entity\Tokens;

class Utilities{
    
    public function __construct(\Doctrine\ORM\EntityManager $em, $logger, $session, $container) {
        $this->em = $em;
        $this->logger = $logger;
        $this->session = $session;
        $this->container = $container;
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
            $token_date_created->modify('+5 minute');
            $current_minute = date('YmdHis');
            if($current_minute > $token_date_created->format('YmdHis')){
                $expired_token = 1;
            }
        }

        return $expired_token;
    }

    
}
