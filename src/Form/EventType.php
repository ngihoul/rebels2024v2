<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\Place;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Entre un nom d\'évènement'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('category', EntityType::class, [
                'class' => EventCategory::class,
                'choice_label' => 'name',
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Sélectionne la date de l\'évènement']),
                ],
                'input' => 'datetime_immutable',
            ])
            ->add('time_meeting', TimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input'  => 'datetime_immutable',
            ])
            ->add('time_from', TimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Sélectionne l\'heure de début']),
                ],
                'input'  => 'datetime_immutable',
            ])
            ->add('time_to', TimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Sélectionne l\'heure de fin']),
                ],
                'input'  => 'datetime_immutable',
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
            ])
            ->add('is_recurrent', CheckboxType::class, [
                'label' => 'Entraînement récurrent',
                'mapped' => false,
                'required' => false,
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
                'mapped' => false
            ])
            ->add('frequency', ChoiceType::class, [
                'choices' => [
                    'Tous les jours' => 'daily',
                    'Toutes les semaines' => 'weekly',
                    'Toutes les deux semaines' => 'biweekly',
                    'Tous les mois' => 'monthly'
                ],
                'required' => false,
                'mapped' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
