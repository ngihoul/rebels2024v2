<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUserToTeam extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', TextType::class, [
                'attr' => [
                    'id' => 'user-autocomplete',
                    'autocomplete' => 'off'
                ],
                'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'add_player.btn']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'team' => null,
            'translation_domain' => 'forms'
        ]);
    }
}
