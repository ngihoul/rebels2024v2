<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isChild = $options['is_child'];
        $userAge = $options['user_age'];
        $isPrivacyPolicyMissing = $options['privacy_policy'];
        $isRoiMissing = $options['roi'];

        $builder
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.firstname.not_blank']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'validators.firstname.length',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.lastname.not_blank']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'validators.lastname.length',
                    ]),
                ],
            ])
            ->add('nationality', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(['message' => 'validators.nationality.not_blank'])
                ],
            ])
            ->add('license_number', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 12,
                        'maxMessage' => 'validators.license_number.length',
                    ]),
                ],
            ])
            ->add('jersey_number', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'validators.jersey_number.type',
                    ]),
                ],
            ])
            ->add('date_of_birth', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'validators.date_of_birth.not_blank']),
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'user.gender.male' => 'M',
                    'user.gender.female' => 'F',
                ],
                'constraints' => [
                    new Choice([
                        'choices' => ['M', 'F'],
                        'message' => 'validators.gender.choice',
                    ]),
                ],
            ])
            ->add('address_street', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.address_street.not_blank']),
                    new Length([
                        'max' => 120,
                        'maxMessage' => 'validators.address_street.length',
                    ]),
                ],
            ])
            ->add('address_number', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.address_number.not_blank']),
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'validators.address_number.length',
                    ]),
                ],
            ])
            ->add('zipcode', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.zipcode.not_blank']),
                    new Length([
                        'max' => 6,
                        'maxMessage' => 'validators.zipcode.length',
                    ]),
                ],
            ])
            ->add('locality', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.locality.not_blank']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'validators.locality.length',
                    ]),
                ],
            ])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(['message' => 'validators.country.not_blank'])
                ],
            ])
            ->add('phone_number', TelType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'validators.phone_number.length',
                    ]),
                ],
            ])
            ->add('mobile_number', TelType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'validators.mobile_number.length',
                    ]),
                ],
            ])
            ->add('profile_picture', FileType::class, [
                'multiple' => false,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'maxSizeMessage' => 'validators.profile_picture.max_size',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'validators.profile_picture.mime_types',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class);

        if ($isChild) {
            $builder->add('email', EmailType::class, [
                'label' => 'children.email',
                'row_attr' => [
                    'class' => 'email'
                ],
                'required' => !$isChild,
                'constraints' => $isChild ? [new Email(['message' => 'validators.email.valid'])] : [new Email(['message' => 'validators.email.valid']), new NotBlank()],
            ]);
        }

        if ($userAge >= 16 && $userAge < 18) {
            $builder->add('can_use_app', CheckboxType::class, [
                'label' => 'children.can_use_app',
                'row_attr' => [
                    'class' => 'can-use-app'
                ],
                'required' => false,
            ]);
        }

        if (!$isChild) {
            $builder->add('newsletter_lfbbs', CheckboxType::class, [
                'required' => false,
            ]);
        }

        if ($isPrivacyPolicyMissing && !$isChild) {
            $builder->add('privacy_policy', CheckboxType::class, [
                'required' => true,
            ]);
        }

        if ($isRoiMissing && !$isChild) {
            $builder->add('internal_rules', CheckboxType::class, [
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms',
            'is_child' => false,
            'user_age' => null,
            'privacy_policy' => false,
            'roi' => false,
        ]);
    }
}
