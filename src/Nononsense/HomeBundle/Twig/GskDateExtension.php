<?php

namespace Nononsense\HomeBundle\Twig;

class GskDateExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('gskdate', array($this, 'gskDate')),
        );
    }

    public function gskDate($string)
    {
        $date=$this->formatDate($string);

        return $date;
    }

    public function formatDate($date)
    {
        $before = array("/01/", "/02/", "/03/", "/04/", "/05/", "/06/", "/07/", "/08/", "/09/", "/10/", "/11/", "/12/");
        $after = array("/Enero/","/Febrero/","/Marzo/","/Abril/","/Mayo/","/Junio/","/Julio/","/Agosto/","/Septiembre/","/Octubre/","/Noviembre/","/Diciembre/");
        return str_replace($before, $after, $date);
    }
}

