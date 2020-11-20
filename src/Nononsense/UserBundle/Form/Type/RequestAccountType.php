<?php
namespace Nononsense\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Nononsense\GroupBundle\Entity\Groups;

class RequestAccountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('mud_id', TextType::class, ['label' => 'MUD_ID', 'required' => true])
            ->add('username', TextType::class, ['label' => 'Nombre y Apellidos', 'required' => true])
            ->add('activeDirectory', CheckboxType::class, ['label' => '¿Posee usted cuenta en Azure Active Directory?', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Solicitar cuenta'])
            ->add('description', TextareaType::class, ['label' => 'Motivo de la solicitud'])
            ->add('groups', EntityType::class, [
                'class' => Groups::class,
                'choice_label' => 'group',
                'multiple'=>'true',
                'label' => 'Seleccionar grupo/s a los que pertenece'
            ]);
    }



}