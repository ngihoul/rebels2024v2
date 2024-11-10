<?php

namespace App\Form;

use App\Entity\License;
use App\Entity\LicenseSubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

class LicenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentYear = date('Y');

        $builder
            ->add('season', ChoiceType::class, [
                'choices' => [$currentYear => $currentYear],
                'disabled' => true,
                'constraints' => [
                    new NotBlank(['message' => 'validators.season']),
                ],
            ])
            ->add('subCategories', EntityType::class, [
                'class' => LicenseSubCategory::class,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
                'by_reference' => false,
                'constraints' => [
                    new NotBlank(['message' => 'validators.license.sub_category.not_blank']),
                    new Count(['min' => 1, 'minMessage' => 'validators.license.sub_category.not_blank']),
                ],
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => License::class,
            'translation_domain' => 'forms'
        ]);
    }
}
