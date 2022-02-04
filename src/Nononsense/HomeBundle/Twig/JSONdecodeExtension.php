<?php

namespace Nononsense\HomeBundle\Twig;

class JSONdecodeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('json_decode', array($this, 'jsonDecode')),
        );
    }

    public function jsonDecode($str,$option = FALSE)
    {
        return json_decode($str,$option);
    }

    public function getName()
    {
        return 'json_decode';
    }
}

