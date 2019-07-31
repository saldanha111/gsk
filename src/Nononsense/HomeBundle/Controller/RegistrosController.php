<?php

namespace Nononsense\HomeBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class RegistrosController extends Controller
{
    public function homeAction()
    {
        return $this->render('NononsenseHomeBundle:Contratos:home.html.twig');
    }
}
