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
            ->add('base_dn', TextType::class, ['label' => 'Base DN', 'required' => true, 'empty_data' => 'dc=demo,dc=local'])
            // ->add('search_dn', TextType::class, ['label' => 'Search DN', 'required' => true, 'empty_data' => 'cn={username},cn=users,dc=demo,dc=local'])
            ->add('dn_string', TextType::class, ['label' => 'Admin DN', 'required' => true, 'empty_data' => 'cn=admin,cn=users,dc=demo,dc=local'])
            ->add('search_password', PasswordType::class, ['label' => 'Admin pass', 'required' => true])
            ->add('mudid', TextType::class, ['label' => 'MUD ID', 'required' => false]);
            // ->add('dn', TextType::class, ['label' => 'DN', 'required' => true])
            // ->add('_password', PasswordType::class, ['label' => 'Contraseña', 'required' => true])
            // ->add('filter', TextType::class, ['label' => 'Filtros', 'required' => false])
            // ->add('querydn', TextType::class, ['label' => 'Query DN']);

    }



}