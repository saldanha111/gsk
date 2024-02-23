<?php

namespace Nononsense\HomeBundle\Twig;

class SlugifyExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('slugify', array($this, 'slugify')),
        );
    }

    public function slugify($string)
    {
        // replace all non letters or digits by -
        $string = preg_replace('/\W+/', '-', $string);

        // trim and lowercase the string
        $string = strtolower(trim($string, '-'));

        // if no value set slug as 'undefined'
        if (empty($string)) {
            $string = 'undefined';
        }

        return $string;
    }

    public function getName()
    {
        return 'slugify';
    }
}

