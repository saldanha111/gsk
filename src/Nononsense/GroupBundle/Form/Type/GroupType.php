<?php
namespace Nononsense\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder 
            ->add('name', 'text', array('required' => true, 'trim' => true))
            ->add('color', 'text', array('required' => true, 'trim' => true))
            ->add('isActive', 'checkbox', array('required' => false))
            ->add('description', 'textarea', array('required' => true, 'trim' => true))
            ->add('save', 'submit', array('label' => 'Guardar grupo', 'translation_domain' => 'messages'));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'groups';
    }
}