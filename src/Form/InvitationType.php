<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invitedTeams', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $eventId = $options['event']->getId();

                    return $er->createQueryBuilder('t')
                        ->leftJoin('t.events', 'te')
                        ->andWhere('te.id IS NULL OR te.id != :eventId')
                        ->setParameter('eventId', $eventId)
                        ->orderBy('t.name', 'ASC');
                },
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
            ])
            ->add('invitedUsers', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $eventId = $options['event']->getId();

                    return $er->createQueryBuilder('u')
                        ->leftJoin('u.events', 'ea', 'WITH', 'ea.event = :eventId')
                        ->andWhere('ea.event IS NULL')
                        ->setParameter('eventId', $eventId)
                        ->orderBy('u.lastname', 'ASC');
                },
                'choice_label' => function ($user) {
                    return $user->getLastname() . ' ' . $user->getFirstname();
                },
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'invited-users-field',
                ],
            ])
            ->add('invite', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'event' => null,
            'translation_domain' => 'forms'
        ]);
    }
}
