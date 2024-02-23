<?php

namespace Nononsense\HomeBundle\Twig;

class Object2ArrayExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('object_to_array', array($this, 'Obj2Array')),
        );
    }

    public function obj2Array($obj)
    {
        $data = array();
        foreach ($obj as $prop => $value){
            $data[$prop] = $value;
        }
        return $data;
    }

    public function getName()
    {
        return 'object_to_array';
    }
}

