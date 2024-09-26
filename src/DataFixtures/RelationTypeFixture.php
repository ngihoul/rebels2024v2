<?php

namespace App\DataFixtures;

use App\Entity\RelationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RelationTypeFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $relationTypesData = [
            'Parent' => 'Parent',
            'Représentant légal' => 'Legal representative',
            'Frère/Soeur' => 'Brother/Sister',
        ];

        foreach ($relationTypesData as $name => $englishTranslation) {
            $relationType = new RelationType();
            $relationType->setName($name);

            $manager->persist($relationType);
            $manager->flush();

            $relationType = $manager->find(RelationType::class, $relationType);

            $relationType->setTranslatableLocale('en');
            $relationType->setName($englishTranslation);

            $manager->persist($relationType);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['relation', 'production'];
    }
}
