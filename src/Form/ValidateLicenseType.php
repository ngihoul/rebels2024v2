<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidateLicenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', NumberType::class, [
                'label' => ' ',
                'required' => false,
                'html5' => true
            ])
            ->add('comment', TextareaType::class, [
                'label' => ' ',
                'required' => false,
            ])
            ->add('approval', SubmitType::class, [
                'attr' => ['class' => 'btn'],
            ])
            ->add('refusal', SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
