<?php
namespace Nononsense\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class ldapType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('dn', TextType::class, ['label' => 'DN', 'required' => true])
            ->add('_password', PasswordType::class, ['label' => 'Contraseña', 'required' => true])
            ->add('filter', TextType::class, ['label' => 'Filtros', 'required' => false])
            ->add('querydn', TextType::class, ['label' => 'Query DN']);

    }



}