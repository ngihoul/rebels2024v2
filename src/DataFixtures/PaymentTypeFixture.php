<?php

namespace App\DataFixtures;

use App\Entity\PaymentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PaymentTypeFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $paymentTypesData = [
            'Totalité par Stripe' => 'Full payment by Stripe',
            'Totalité par virement bancaire' => 'Full payment by bank transfer',
            'Via un plan de paiement personnalisé' => 'Via a personalized payment plan',
        ];

        foreach ($paymentTypesData as $name => $englishTranslation) {
            $paymentType = new PaymentType();
            $paymentType->setName($name);

            $manager->persist($paymentType);
            $manager->flush();

            $paymentType = $manager->find(PaymentType::class, $paymentType);

            $paymentType->setTranslatableLocale('en');
            $paymentType->setName($englishTranslation);

            $manager->persist($paymentType);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['payment', 'production'];
    }
}
