<?php

namespace Nononsense\HomeBundle\Controller;
use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('NononsenseHomeBundle:Contratos:home.html.twig');
    }
    
}
