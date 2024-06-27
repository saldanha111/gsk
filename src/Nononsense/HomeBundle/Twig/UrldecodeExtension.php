<?php

namespace Nononsense\HomeBundle\Twig;

class UrldecodeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('url_decode', array($this, 'urlDecode')),
        );
    }

    public function urlDecode($str)
    {
        return rawurldecode($str);
    }

    public function getName()
    {
        return 'url_decode';
    }
}

