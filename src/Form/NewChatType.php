<?php

namespace App\Form;

use App\Entity\User;
use App\Services\UserServices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class NewChatType extends AbstractType
{
    private $userServices;

    public function __construct(UserServices $userServices)
    {
        $this->userServices = $userServices;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usersToAdd = $this->userServices->getUsersToAddToChat($options['currentUser']);

        $builder
            ->add('members', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'choices' => $usersToAdd,
                'choice_label' => function ($user) {
                    return "<img src='{$user->getImage()}'><p>{$user->getFullName()}</p>";
                },
            ])
            ->add('name', TextType::class, [
                'label' => 'Chat name',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Chat image',
                'constraints' => [
                    new Image([
                        'maxSize' => '4M',
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ]),
                ],
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpg, image/jpeg, image/png',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            $resolver->setRequired('currentUser'),
            $resolver->setAllowedTypes('currentUser', User::class),
        ]);
    }

    public function getBlockPrefix()
    {
        return "new_chat";
    }
}
