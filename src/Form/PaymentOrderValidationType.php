<?php

namespace App\Form;

use App\Entity\PaymentOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

// TODO : A traduire

class PaymentOrderValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $paymentOrder = $options['paymentOrder'];

        $builder
            ->add('value_date', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(),
                'constraints' => [
                    new NotBlank(['message' => 'validators.date_of_birth.not_blank']),
                    new LessThanOrEqual([
                        'value' => (new \DateTime()),
                        'message' => 'La date doit être égale ou inférieure à la date du jour',
                    ])
                ],
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentOrder::class,
            'paymentOrder' => null,
            'translation_domain' => 'forms',
        ]);
    }
}
