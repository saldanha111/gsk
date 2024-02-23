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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Nononsense\GroupBundle\Entity\GroupUsers;
use Doctrine\ORM\EntityRepository;

class RemoveRequestAccountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('_password', PasswordType::class, ['label' => 'Firma', 'required' => true, 'mapped' => false])
            ->add('groups', EntityType::class, [
                'class' => GroupUsers::class,
                'choice_label' => function ($userGroup) {
                    return $userGroup->getGroup()->getName();
                },
                'multiple'=>'true',
                'label' => 'Seleccionar grupo/s de los que quiera darse de baja',
                'mapped' => true,
                'required' => true,
                'query_builder' => function(EntityRepository $repository) use ($options) {
                    return $repository->createQueryBuilder('c')
                            ->where('c.user = :user')
                            ->setParameter('user', $options['data']->getId()); //Proxies\__CG__\App\Entity\Category
                }
            ]);
    }



}