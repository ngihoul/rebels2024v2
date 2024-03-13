<?php

namespace App\Form;

use App\Entity\Message;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.message.title.not_blank']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'validators.message.title.length'
                    ])
                ]
            ])
            ->add('titleEnglish', TextType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'validators.message.content.not_blank'
                    ])
                ]
            ])
            ->add('contentEnglish', TextType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('sentToTeams', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
            ])
            ->add('sentToUsers', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.lastname', 'ASC');
                },
                'choice_label' => function ($user) {
                    return $user->getLastname() . ' ' . $user->getFirstname();
                },
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'invited-users-field',
                ],
            ])
            ->add('sent_by_mail', CheckboxType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
