<?php
namespace Nononsense\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\optionsResolver\OptionsResolverInterface;

class ResetPassType extends AbstractType
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder 
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options'  => array('label' => 'Contraseña', 'translation_domain' => 'messages'),
                'second_options' => array('label' => 'Confirmar contraseña', 'translation_domain' => 'messages'),
                )
            )
            ->add('save', 'submit', array('label' => 'Restablecer contraseña', 'translation_domain' => 'messages'));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'resetPasssword';
    }
}