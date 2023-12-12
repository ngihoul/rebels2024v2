<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUserToTeam extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return sprintf('%s %s - %s - %s', $user->getFirstname(), $user->getLastname(), $user->getGender(), $user->getDateOfBirth()->format('d-m-Y'));
                },
                'label' => 'Ajouter un joueur',
                'query_builder' => function (EntityRepository $entityRepository) use ($options) {
                    return $entityRepository->createQueryBuilder('u')
                        ->andWhere(':team NOT MEMBER OF u.teams')
                        ->setParameter('team', $options['team'])
                        ->orderBy('u.lastname', 'ASC');
                },
            ])
            ->add('save', SubmitType::class, ['label' => 'Ajouter']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'team' => null,
        ]);
    }
}
