<?php
namespace Nononsense\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\optionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    private $_admin;
    
    /**
     * @param boolean $admin
     */
    public function __construct($admin)
    {
        $this->_admin = $admin;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder 
            ->add('username', 'text', array('required' => true, 'trim' => true))
            ->add('name', 'text', array('required' => true, 'trim' => true))
            ->add('phone', 'text', array('trim' => true, 'required' => false))
            ->add('position', 'text', array('trim' => true, 'required' => false))            
            ->add('description', 'textarea', array('required' => true, 'trim' => true))
            ->add('save', 'submit', array('label' => 'Guardar', 'translation_domain' => 'messages'));
            
            
            if ($this->_admin) {
                $builder->add('isActive', 'checkbox', array('required' => false))
                ->add('email', 'repeated', array(
                                 'type' => 'email',
                                 'invalid_message' => 'Los campos de email deben coincidir.',
                                 'required' => true,
                                 'first_options'  => array('label' => 'Email', 'translation_domain' => 'messages'),
                                 'second_options' => array('label' => 'Confirmar email', 'translation_domain' => 'messages')
                             )
                );
            }
            
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $user = $event->getData();
                $form = $event->getForm();
                
                if (!$user || null === $user->getId()) {
                    $form->add('password', 'repeated', array(
                        'type' => 'password',
                        'invalid_message' => 'Los campos de contraseña deben coincidir',
                        'required' => true,
                        'first_options'  => array('label' => 'Contraseña', 'translation_domain' => 'messages'),
                        'second_options' => array('label' => 'Confirmar contraseña', 'translation_domain' => 'messages'),
                        )
                    );
                    $form->add('photo', 'textarea', array('required' => true, 'trim' => true));
                }
            });
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }
}