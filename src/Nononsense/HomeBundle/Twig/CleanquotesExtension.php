<?php

namespace Nononsense\HomeBundle\Twig;

class CleanquotesExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('clean_quotes', array($this, 'cleanQuotes')),
        );
    }

    public function cleanQuotes($string)
    {
        $search = array('&apos;');
        $replace = array('\'');
        return str_replace($search, $replace, $string);
    }

    public function getName()
    {
        return 'clean_quotes';
    }
}

