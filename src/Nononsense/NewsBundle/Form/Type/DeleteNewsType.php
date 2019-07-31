<?php
namespace Nononsense\NewsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DeleteNewsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder 
            ->add('save', 'submit', array('label' => 'Delete News Entry', 'translation_domain' => 'messages'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'DeleteNews';
    }
}