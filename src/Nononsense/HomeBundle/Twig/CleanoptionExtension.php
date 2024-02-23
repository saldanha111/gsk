<?php

namespace Nononsense\HomeBundle\Twig;

class CleanoptionExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('clean_option', array($this, 'cleanOption')),
        );
    }

    public function cleanOption($string, $options = '')
    {
        $initial = substr($string, 0, 1);
        if ($initial == '@' || $initial == '#'){
            return substr($string, 1);
        } else {
            return $string;
        }
    }

    public function getName()
    {
        return 'clean_option';
    }
}

