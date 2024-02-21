<?php

namespace App\DataFixtures;

use App\Entity\License;
use App\Entity\User;
use App\Entity\LicenseSubCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LicenseFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create("fr_BE");

        // Get all users
        $users = $manager->getRepository(User::class)->findAll();

        // Get all license sub-categories
        $subCategories = $manager->getRepository(LicenseSubCategory::class)->findAll();

        foreach ($users as $user) {
            // Get all years for which the user has licenses
            $existingYears = $this->getExistingYears($user, $manager);

            // Generate licenses for each year the user doesn't have a license for
            $yearsToGenerate = range(date('Y'), date('Y') - 2); // Change the range if needed
            foreach ($yearsToGenerate as $year) {
                if (!in_array($year, $existingYears)) {
                    $license = new License();
                    $license->setSeason($year);
                    $license->setStatus($faker->numberBetween(1, 5));
                    $license->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
                    $license->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
                    $license->setUser($user);
                    $license->setPrice($faker->randomFloat(2, 10, 100));

                    // Shuffle the sub-categories array to randomize the order
                    shuffle($subCategories);

                    // Add a random number of sub-categories to the license
                    $numSubCategories = mt_rand(1, count($subCategories));
                    for ($j = 0; $j < $numSubCategories; $j++) {
                        $license->addSubCategory($subCategories[$j]);
                    }

                    $manager->persist($license);
                }
            }
        }

        $manager->flush();
    }

    /**
     * Get the years for which the user already has licenses.
     *
     * @param User          $user
     * @param ObjectManager $manager
     *
     * @return array
     */
    private function getExistingYears(User $user, ObjectManager $manager): array
    {
        $existingYears = [];
        $licenses = $manager->getRepository(License::class)->findBy(['user' => $user]);
        foreach ($licenses as $license) {
            $existingYears[] = $license->getSeason();
        }

        return $existingYears;
    }

    public function getDependencies()
    {
        return [
            UserFixture::class,
            LicenseCategoryFixture::class
        ];
    }
}
