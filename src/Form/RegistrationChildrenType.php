<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Country;
use App\Entity\RelationType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThan;

class RegistrationChildrenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('children', CollectionType::class, [
                'entry_type' => ChildType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'data' => [new User()],
                'entry_options' => [
                    'label' => false,
                ],
                'attr' => [
                    'class' => 'children-collection',
                ],
            ]);
    }
}

class ChildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('relation_type', EntityType::class, [
                'label' => 'children.relation_type',
                'row_attr' => [
                    'class' => 'relation'
                ],
                'class' => RelationType::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(['message' => 'validators.relation.not_blank'])
                ],
                'mapped' => false,
            ])
            ->add('firstname', TextType::class, [
                'label' => 'user.firstname',
                'row_attr' => [
                    'class' => 'firstname'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validators.firstname.not_blank']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'validators.firstname.length',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'user.lastname',
                'row_attr' => [
                    'class' => 'lastname'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validators.lastname.not_blank']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'validators.lastname.length',
                    ]),
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'user.gender.label',
                'row_attr' => [
                    'class' => 'gender'
                ],
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
            ->add('date_of_birth', DateType::class, [
                'label' => 'user.date_of_birth',
                'row_attr' => [
                    'class' => 'date-of-birth'
                ],
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'validators.date_of_birth.not_blank']),
                    new GreaterThan([
                        'value' => (new \DateTime())->sub(new \DateInterval('P18Y')),
                        'message' => 'validators.date_of_birth.too_old',
                    ]),
                ],
            ])
            ->add('nationality', EntityType::class, [
                'label' => 'user.nationality',
                'row_attr' => [
                    'class' => 'nationality'
                ],
                'class' => Country::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(['message' => 'validators.nationality.not_blank'])
                ],
            ])
            ->add('license_number', TextType::class, [
                'label' => 'user.license_number',
                'row_attr' => [
                    'class' => 'license-number'
                ],
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 12,
                        'maxMessage' => 'validators.license_number.length',
                    ]),
                ],
            ])
            ->add('jersey_number', NumberType::class, [
                'label' => 'user.jersey_number',
                'row_attr' => [
                    'class' => 'jersey-number'
                ],
                'required' => false,
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'validators.jersey_number.type',
                    ]),
                ],
            ])
            ->add('profile_picture', FileType::class, [
                'label' => 'user.profile_picture.label',
                'row_attr' => [
                    'class' => 'profile-picture'
                ],
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
            ->add('address_street', TextType::class, [
                'label' => 'user.address_street',
                'row_attr' => [
                    'class' => 'address-street'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validators.address_street.not_blank']),
                    new Length([
                        'max' => 120,
                        'maxMessage' => 'validators.address_street.length',
                    ]),
                ],
            ])
            ->add('address_number', TextType::class, [
                'label' => 'user.address_number',
                'row_attr' => [
                    'class' => 'address-number'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validators.address_number.not_blank']),
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'validators.address_number.length',
                    ]),
                ],
            ])
            ->add('zipcode', TextType::class, [
                'label' => 'user.zipcode',
                'row_attr' => [
                    'class' => 'zipcode'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validators.zipcode.not_blank']),
                    new Length([
                        'max' => 6,
                        'maxMessage' => 'validators.zipcode.length',
                    ]),
                ],
            ])
            ->add('locality', TextType::class, [
                'label' => 'user.locality',
                'row_attr' => [
                    'class' => 'locality'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validators.locality.not_blank']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'validators.locality.length',
                    ]),
                ],
            ])
            ->add('country', EntityType::class, [
                'label' => 'user.country',
                'row_attr' => [
                    'class' => 'country'
                ],
                'class' => Country::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(['message' => 'validators.country.not_blank'])
                ],
            ])
            ->add('phone_number', TelType::class, [
                'label' => 'user.phone_number',
                'row_attr' => [
                    'class' => 'phone-number'
                ],
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'validators.phone_number.length',
                    ]),
                ],
            ])
            ->add('mobile_number', TelType::class, [
                'label' => 'user.mobile_number',
                'row_attr' => [
                    'class' => 'mobile-number'
                ],
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'validators.mobile_number.length',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'user.email',
                'row_attr' => [
                    'class' => 'email'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'validators.email.not_blank']),
                    new Email(['message' => 'validators.email.valid']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms'
        ]);
    }
}
