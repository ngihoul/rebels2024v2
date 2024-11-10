<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixture extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_BE');

        $countryRepository = $manager->getRepository(Country::class);

        for ($i = 0; $i < 200; $i++) {
            $user = new User();
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);

            // Nationality & Address Country
            $alpha2 = $faker->countryCode();
            $country = $countryRepository->findOneBy(['alpha2' => $alpha2]);

            if (!$country instanceof Country) {
                $alpha2 = 'be';
                $country = $countryRepository->findOneBy(['alpha2' => $alpha2]);
            }

            $user->setNationality($country);
            $user->setCountry($country);

            $user->setLicenseNumber($faker->randomNumber(6));
            $user->setJerseyNumber($faker->numberBetween(1, 99));
            $user->setDateOfBirth($faker->dateTimeBetween('-50 years', '-18 years'));
            $user->setGender($faker->randomElement(['M', 'F']));
            $user->setAddressStreet($faker->streetAddress);
            $user->setAddressNumber($faker->buildingNumber);
            $user->setZipcode($faker->postcode);
            $user->setLocality($faker->city);
            $user->setPhoneNumber($faker->phoneNumber);
            $user->setMobileNumber($faker->phoneNumber);
            $user->setEmail($faker->unique()->email);
            $user->setPassword($faker->password);
            $user->setRoles(['ROLE_USER']);
            $user->setProfilePicture($faker->imageUrl());
            $user->setNewsletterLfbbs($faker->boolean);
            $user->setInternalRules($faker->boolean);
            $user->setIsBanned($faker->boolean);
            $user->setIsArchived($faker->boolean);
            $user->setIsVerified($faker->boolean);
            $user->setPrivacyPolicy(true);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['user'];
    }

    public function getDependencies()
    {
        return [
            CountryFixture::class,
        ];
    }
}
