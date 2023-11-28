<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ValidateLicenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', TextType::class, [
                'label' => ' ',
                'required' => false
            ])
            ->add('comment', TextareaType::class, [
                'label' => ' ',
                'required' => false,
            ])
            ->add('approval', SubmitType::class, [
                'label' => "Valider",
                'attr' => ['class' => 'btn'], // Vous pouvez personnaliser les classes CSS si nécessaire
            ])
            ->add('refusal', SubmitType::class, [
                'label' => 'Refuser',
                'attr' => ['class' => 'btn cta'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
