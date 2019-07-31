<?php
namespace Nononsense\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DataType extends AbstractType
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
            ->add('email', 'repeated', array(
                    'type' => 'email',
                    'invalid_message' => 'The email fields must match.',
                    'required' => true,
                    'first_options'  => array('label' => 'Email'),
                    'second_options' => array('label' => 'Confirm email')
                    )
                )
            ->add('phone', 'text', array('trim' => true))
            ->add('position', 'text', array('trim' => true))
            ->add('description', 'textarea', array('required' => true, 'trim' => true))
            ->add('save', 'submit', array('label' => 'Save User', 'translation_domain' => 'messages'));
            
            if ($this->_admin) {
                $builder->add('isActive', 'checkbox', array('required' => false));
            }

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }
}