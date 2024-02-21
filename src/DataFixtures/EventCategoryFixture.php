<?php

namespace App\DataFixtures;

use App\Entity\EventCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class EventCategoryFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $categoriesData = [
            'Entrainement' => 'Training',
            'Match' => 'Game',
            'Tournoi' => 'Tournament',
            'Fête' => 'Party',
            'Divers' => 'Other'
        ];

        foreach ($categoriesData as $name => $englishTranslation) {
            $category = new EventCategory();
            $category->setName($name);

            $manager->persist($category);
            $manager->flush();

            $category = $manager->find(EventCategory::class, $category);

            $category->setTranslatableLocale('en');
            $category->setName($englishTranslation);

            $manager->persist($category);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['event_category'];
    }
}
