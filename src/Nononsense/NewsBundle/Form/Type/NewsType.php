<?php
namespace Nononsense\NewsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder 
            ->add('title', 'text', array('required' => true, 'trim' => true))
            ->add('isActive', 'checkbox', array('required' => false))
            ->add('body', 'textarea', array('required' => true, 'trim' => true))
            ->add('save', 'submit', array('label' => 'Save News', 'translation_domain' => 'messages'));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'news';
    }
}