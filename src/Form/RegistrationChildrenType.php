<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationChildrenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('children', CollectionType::class, [
                'entry_type' => ChildType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'data' => [new User()],
                'attr' => [
                    'class' => 'children-collection',
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['children']) && is_array($data['children'])) {
                foreach ($data['children'] as $key => $childData) {
                    if (isset($childData['can_use_app']) && $childData['can_use_app']) {
                        $form->get('children')->get($key)->add('email', EmailType::class, [
                            'label' => 'user.email',
                            'row_attr' => [
                                'class' => 'email'
                            ],
                            'constraints' => [
                                new Email(['message' => 'validators.email.valid']),
                            ],
                            'required' => false,
                        ]);
                    }
                }
            }
        });
    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms'
        ]);
    }
}
