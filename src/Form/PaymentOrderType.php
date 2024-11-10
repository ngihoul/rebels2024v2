<?php

namespace App\Form;

use App\Entity\PaymentOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
                    new NotBlank(['message' => 'validators.payment.amount.not_blank']),
                    new GreaterThan(['value' => 0, 'message' => 'validators.payment.amount.positive'])
                ],
                'label' => 'payment.order.amount'
            ])
            ->add('due_date', DateType::class, [
                'row_attr' => [
                    'class' => 'due-date'
                ],
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'validators.payment.due_date.not_blank']),
                ],
                'label' => 'payment.order.due_date'
            ])
            ->add('comment', TextareaType::class, [
                'row_attr' => [
                    'class' => 'comment'
                ],
                'required' => false,
                'label' => 'payment.order.comment'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentOrder::class,
            'translation_domain' => 'forms'
        ]);
    }
}
