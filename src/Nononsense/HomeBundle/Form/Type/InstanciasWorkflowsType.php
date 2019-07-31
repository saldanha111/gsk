<?php
namespace Nononsense\HomeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class InstanciasWorkflowsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder 
            ->add('isActive', 'checkbox', array('required' => false));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Contratos';
    }
}