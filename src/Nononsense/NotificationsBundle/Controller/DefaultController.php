<?php

namespace Nononsense\NotificationsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('NononsenseNotificationsBundle:Default:index.html.twig');
    }
}
