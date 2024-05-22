<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CountryFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $jsonFilePath = __DIR__ . '/data/countries.json';

        $jsonContent = file_get_contents($jsonFilePath);

        $countriesData = json_decode($jsonContent, true);

        foreach ($countriesData as $countryData) {
            $country = new Country();
            $country->setAlpha2($countryData['alpha2']);
            $country->setName($countryData['fr']);

            $manager->persist($country);
            $manager->flush();

            $country = $manager->find(Country::class, $country);
            $country->setTranslatableLocale('en');
            $country->setName($countryData['en']);

            $manager->persist($country);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['country', 'production'];
    }
}
