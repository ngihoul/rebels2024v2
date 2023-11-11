<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadLicenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uploaded_file', FileType::class, [
                'label' => 'Ajoute ta demande de licence ici',
                'multiple' => false,
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'maxSizeMessage' => 'La taille de la photo de profil ne doit pas dépasser 2Mo',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Le format de la photo de profil doit être jpg, jpeg, gif, png ou pdf',
                    ])
                ],
            ])
            ->add('confirm_rules', CheckboxType::class, [
                'label' => 'Je confirme avoir lu le Règlement d\'ordre intérieur',
                'required' => true, // Vous pouvez définir cette option en fonction de vos besoins
            ])
            ->add('confirm_data', CheckboxType::class, [
                'label' => 'Je confirme que toutes mes données sont correctes et que tous les champs sont complètés',
                'required' => true, // Vous pouvez définir cette option en fonction de vos besoins
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
