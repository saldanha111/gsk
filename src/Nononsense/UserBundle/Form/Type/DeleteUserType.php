<?php
namespace Nononsense\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DeleteUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder 
            ->add('save', 'submit', array('label' => 'Delete User', 'translation_domain' => 'messages'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'DeleteUser';
    }
}