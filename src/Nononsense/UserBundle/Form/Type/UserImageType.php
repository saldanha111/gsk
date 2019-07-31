<?php
namespace Nononsense\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder 
            ->add('photo', 'textarea', array('required' => true, 'trim' => true))
            ->add('save', 'submit', array('label' => 'Save Image', 'translation_domain' => 'messages'));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'userImage';
    }
}