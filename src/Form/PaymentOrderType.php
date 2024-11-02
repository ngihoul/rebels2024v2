<?php

// TODO : à traduire

namespace App\Form;

use App\Entity\PaymentOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', NumberType::class, [
                'row_attr' => [
                    'class' => 'amount'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le champ est obligatoire']),
                    new GreaterThan(['value' => 0, 'message' => 'Le montant doit être positif'])
                ]
            ])
            ->add('due_date', DateType::class, [
                'row_attr' => [
                    'class' => 'due-date'
                ],
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Le champ est obligatoire']),
                ],
            ])
            ->add('comment', TextareaType::class, [
                'row_attr' => [
                    'class' => 'comment'
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentOrder::class,
        ]);
    }
}
