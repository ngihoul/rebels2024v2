<?php

namespace App\Form;

use App\Entity\Team;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('coach', EntityType::class, [
                'class' => 'App\Entity\User',
                'choice_label' => function ($user) {
                    return $user->getLastname() . ' ' . $user->getFirstname();
                },
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.lastname', 'ASC');
                },
            ])
            ->add('assistant', EntityType::class, [
                'class' => 'App\Entity\User',
                'choice_label' => function ($user) {
                    return $user->getLastname() . ' ' . $user->getFirstname();
                },
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.lastname', 'ASC');
                },
            ])
            ->add('logo', FileType::class, [
                'multiple' => false,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'maxSizeMessage' => 'validators.logo.size',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'validators.logo.mime_types',
                    ])
                ],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
            'translation_domain' => 'forms'
        ]);
    }
}
