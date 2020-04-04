<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChatType extends AbstractType
{
    public function __construct(SessionInterface $session)
    {
        $this->id = $session->get('user')->getId();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('members', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $eR) {
                    //Specifies that in form will not be currently logged user
                    return $eR->createQueryBuilder('u')
                        ->andWhere("u.id != :userId")
                        ->setParameter('userId', $this->id);
                },
                'choice_label' => function ($user) {
                    return $user->getName() . ' ' . $user->getSurname();
                },
                'multiple' => true,
                'expanded' => true
            ])
            ->add('name', TextType::class, [
                'required' => false
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
    public function getBlockPrefix()
    {
        return 'chatCreate';
    }
}
