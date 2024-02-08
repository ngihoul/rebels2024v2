<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_BE');

        for ($i = 0; $i < 100; $i++) {
            $user = new User();
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setNationality($faker->country);
            $user->setLicenseNumber($faker->randomNumber(6));
            $user->setJerseyNumber($faker->numberBetween(1, 99));
            $user->setDateOfBirth($faker->dateTimeBetween('-50 years', '-18 years'));
            $user->setGender($faker->randomElement(['M', 'F']));
            $user->setAddressStreet($faker->streetAddress);
            $user->setAddressNumber($faker->buildingNumber);
            $user->setZipcode($faker->postcode);
            $user->setLocality($faker->city);
            $user->setCountry($faker->country);
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

            $manager->persist($user);
        }

        $manager->flush();
    }
}
