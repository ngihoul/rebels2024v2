<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Country;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                    new LessThanOrEqual([
                        'value' => (new \DateTime())->sub(new \DateInterval('P18Y')),
                        'message' => 'validators.date_of_birth.too_young',
                    ]),
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
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.email.not_blank']),
                    new Email(['message' => 'validators.email.valid']),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'validators.password.match',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'validators.password.not_blank']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'validators.password.length',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!?#@,.:;]).{8,}$/',
                        'message' => 'validators.password.regex',
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
            ->add('newsletter_lfbbs', CheckboxType::class, [
                'required' => false,
            ])
            ->add('internal_rules', CheckboxType::class, [
                'required' => true,
            ])
            ->add('privacy_policy', CheckboxType::class, [
                'required' => true,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms'
        ]);
    }
}
