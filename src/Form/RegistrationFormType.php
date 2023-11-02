<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre prénom.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le prénom ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom de famille.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom de famille ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('nationality', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nationalité.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'La nationalité ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('license_number', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 12,
                        'maxMessage' => 'Le numéro de licence ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('jersey_number', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'Le numéro de maillot doit être un nombre.',
                    ]),
                ],
            ])
            ->add('date_of_birth', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner votre date de naissance.']),
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'M',
                    'Femme' => 'F',
                ],
                'constraints' => [
                    new Choice([
                        'choices' => ['M', 'F'],
                        'message' => 'Veuillez sélectionner un genre valide.',
                    ]),
                ],
            ])
            ->add('address_street', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse.']),
                    new Length([
                        'max' => 120,
                        'maxMessage' => 'L\'adresse ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('address_number', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer le numéro de votre adresse.']),
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le numéro de l\'adresse ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('zipcode', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre code postal.']),
                    new Length([
                        'max' => 6,
                        'maxMessage' => 'Le code postal ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('locality', TextType::class, [
                'constraints' => [
                    new Notblank(['message' => 'Veuillez entrer votre localité.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'La localité ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('country', TextType::class, [
                'constraints' => [
                    new Notblank(['message' => 'Veuillez entrer votre pays.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le pays ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('phone_number', TelType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le numéro de téléphone ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('mobile_number', TelType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le numéro de portable ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse e-mail.']),
                    new Email(['message' => 'Adresse e-mail invalide.']),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les champs de mot de passe doivent correspondre.',
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('profile_picture', FileType::class, [
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'maxSizeMessage' => 'Le fichier est trop volumineux. La taille maximale autorisée est de {{ limit }}.',
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier image valide.',
                    ]),
                ],
            ])
            ->add('newsletter_lfbbs', CheckboxType::class, [
                'required' => false,
            ])
            ->add('internal_rules', CheckboxType::class, [
                'required' => false,
            ])
            ->add('Inscription', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
