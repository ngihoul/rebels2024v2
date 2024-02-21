<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PlaceFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $place = [
            "Liege Rebels" => [
                "address" => "Voie Mélotte",
                "zipcode" => "4030",
                "locality" => "Grivegnée",
                "country" => "Belgique"
            ],
            "Seraing Brown Boys" => [
                "address" => "Rue des Roselières",
                "zipcode" => "4101",
                "locality" => "Jemeppe",
                "country" => "Belgique"
            ],
            "Marche-en-Famenne Cracks" => [
                "address" => "Rue Victor Libert  36",
                "zipcode" => "6900",
                "locality" => "Marche-en-Famenne",
                "country" => "Belgique"
            ],
            "Andenne Black Bears" => [
                "address" => "Rue sous Meuse",
                "zipcode" => "5300",
                "locality" => "Andenne",
                "country" => "Belgique"
            ]
        ];
    }

    public static function getGroups(): array
    {
        return ['place'];
    }
}
