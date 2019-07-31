<?php

namespace Nononsense\HomeBundle\Twig;

class SearchExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('search', array($this, 'search')),
        );
    }

    public function search($string, $needle)
    {
        // replace all non letters or digits by -
        $found = strpos($string, $needle);

        // trim and lowercase the string
        $string = strtolower(trim($string, '-'));

        // if no value set slug as 'undefined'
        if ($found === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getName()
    {
        return 'search';
    }
}

