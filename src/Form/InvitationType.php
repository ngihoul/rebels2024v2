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
                    // Utilisez le nom correct de l'association pour les équipes
                    $eventId = $options['event']->getId();

                    return $er->createQueryBuilder('t')
                        ->leftJoin('t.events', 'te')
                        ->andWhere('te.id IS NULL OR te.id != :eventId')
                        ->setParameter('eventId', $eventId);
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
                        ->leftJoin('u.events', 'ea')
                        ->andWhere('ea.event IS NULL OR ea.event != :eventId')
                        ->setParameter('eventId', $eventId);
                },
                'choice_label' => function ($user) {
                    return $user->getFirstname() . ' ' . $user->getLastname();
                },
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
            ])
            ->add('invite', SubmitType::class, ['label' => 'Invite']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'event' => null,
        ]);
    }
}
