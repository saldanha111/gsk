<?php

namespace Nononsense\GroupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('NononsenseGroupBundle:Default:index.html.twig', array('name' => $name));
    }
}
