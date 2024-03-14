<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.place.not_blank']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'validators.place.length',
                    ]),
                ]
            ])
            ->add('address_street', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.address_street.not_blank']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'validators.address_street.length',
                    ]),
                ]
            ])
            ->add('address_number', TextType::class, [
                'required' => false,
            ])
            ->add('address_zipcode', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.zipcode.not_blank']),
                    new Length([
                        'max' => 6,
                        'maxMessage' => 'validators.zipcode.length',
                    ]),
                ],
            ])
            ->add('address_locality', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'validators.locality.not_blank']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'validators.locality.length',
                    ]),
                ],
            ])
            ->add('address_country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(['message' => 'validators.country.not_blank'])
                ],
            ])
            ->add('submit', SubmitType::class);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
            'translation_domain' => 'forms'
        ]);
    }
}
