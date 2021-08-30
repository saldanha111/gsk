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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('email', EmailType::class, ['label' => 'Email', 'required' => true])
            ->add('requestType', ChoiceType::class, [
                'label' => 'Tipo de solicitud',
                'choices'  => [
                    1 => 'Alta',
                    0 => 'Baja',
                ],
            ])
            //->add('activeDirectory', CheckboxType::class, ['data' => true])
            //->add('save', SubmitType::class, ['label' => 'Solicitar cuenta'])
            ->add('_password', PasswordType::class, ['label' => 'Firma', 'required' => true, 'mapped' => false])
            ->add('description', TextareaType::class, ['label' => 'Motivo de la solicitud'])
            ->add('bulk', TextareaType::class, ['label' => 'MUD_IDs', 'mapped' => false, 'required' => true])
            ->add('request', EntityType::class, [
                'class' => Groups::class,
                'choice_label' => 'group',
                'multiple'=>'true',
                'label' => 'Seleccionar grupo/s a los que pertenece',
                'mapped' => false,
                'required' => true
            ]);
    }



}