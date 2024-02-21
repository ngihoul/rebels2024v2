<?php

namespace App\DataFixtures;

use App\Entity\LicenseCategory;
use App\Entity\LicenseSubCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class LicenseCategoryFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $categoriesData = [
            'Joueur' => 'Player',
            'Club officiel' => 'Club official',
            'Officiel' => 'Official',
        ];

        $categoryIds = [];

        foreach ($categoriesData as $name => $englishTranslation) {
            $category = new LicenseCategory();
            $category->setName($name);

            $manager->persist($category);
            $manager->flush();

            $category = $manager->find(LicenseCategory::class, $category);

            $category->setTranslatableLocale('en');
            $category->setName($englishTranslation);

            $manager->persist($category);
            $manager->flush();

            $categoryIds[$name] = $category->getId();
        }

        // LicenseSubCategory Fixture
        $subCategoriesData = [
            ['name' => 'Baseball', 'categoryId' => $categoryIds['Joueur'], 'value' => 1, 'en' => 'Baseball'],
            ['name' => 'Softball', 'categoryId' => $categoryIds['Joueur'], 'value' => 2, 'en' => 'Softball'],
            ['name' => 'Slowpitch', 'categoryId' => $categoryIds['Joueur'], 'value' => 3, 'en' => 'Slowpitch'],
            ['name' => 'Récréant', 'categoryId' => $categoryIds['Joueur'], 'value' => 4, 'en' => 'Recreational'],
            ['name' => 'Baseball 5', 'categoryId' => $categoryIds['Joueur'], 'value' => 5, 'en' => 'Baseball 5'],
            ['name' => 'Assistant coach', 'categoryId' => $categoryIds['Club officiel'], 'value' => 6, 'en' => 'Assistant coach'],
            ['name' => 'Coach Niv1 - Niv2', 'categoryId' => $categoryIds['Club officiel'], 'value' => 7, 'en' => 'Coach Level 1 - Level 2'],
            ['name' => 'Sympathisant', 'categoryId' => $categoryIds['Club officiel'], 'value' => 8, 'en' => 'Supporter'],
            ['name' => 'Administrateur', 'categoryId' => $categoryIds['Club officiel'], 'value' => 9, 'en' => 'Administrator'],
            ['name' => 'Arbitre fédéral', 'categoryId' => $categoryIds['Officiel'], 'value' => 10, 'en' => 'Federal referee'],
            ['name' => 'Arbitre régional', 'categoryId' => $categoryIds['Officiel'], 'value' => 11, 'en' => 'Regional referee'],
            ['name' => 'Scoreur fédéral', 'categoryId' => $categoryIds['Officiel'], 'value' => 12, 'en' => 'Federal scorer'],
            ['name' => 'Scoreur régional', 'categoryId' => $categoryIds['Officiel'], 'value' => 13, 'en' => 'Regional scorer']
        ];

        foreach ($subCategoriesData as $data) {
            $subCategory = new LicenseSubCategory();
            $subCategory->setName($data['name']);
            $subCategory->setValue($data['value']);

            $parentCategory = $manager->getRepository(LicenseCategory::class)->find($data['categoryId']);
            $subCategory->setCategory($parentCategory);

            $manager->persist($subCategory);
            $manager->flush();

            $subCategory = $manager->find(LicenseSubCategory::class, $subCategory);

            $subCategory->setTranslatableLocale('en');
            $subCategory->setName($data['en']);

            $manager->persist($subCategory);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['category'];
    }
}
