<?php
namespace Nononsense\NotificationsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ComposeType extends AbstractType
{
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'userid'         => '',
        ));
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $id = $options['userid'];
            $builder 
            ->add('subject', 'text', array('required' => true, 'trim' => true))
            ->add('groups', 'entity', array(
                                            'class' => 'Nononsense\GroupBundle\Entity\Groups',
                                            'query_builder' => function(EntityRepository $g) use($id) {
                                                return $g->createQueryBuilder('g')
                                                         ->join('g.users', 'gu')
                                                         ->where('gu.user = :id')
                                                         ->setParameter('id', $id)
                                                         ->orderBy('g.name', 'ASC');
                                            },
                                            'property' => 'name',
                                            'multiple' => true
                                          )
                )
            ->add('body', 'textarea', array('required' => true, 'trim' => true))
            ->add('save', 'submit', array('label' => 'Send', 'translation_domain' => 'messages'));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'compose';
    }
}