<?php

namespace Nononsense\HomeBundle\Twig;

class ExistfileExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('file_exists', array($this, 'existFile')),
        );
    }

    public function existFile($str)
    {
        return file_exists($str);
    }

    public function getName()
    {
        return 'file_exists';
    }
}

