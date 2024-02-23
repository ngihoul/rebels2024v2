<?php

namespace App\DataFixtures;

use App\Entity\Place;
use App\Repository\CountryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PlaceFixture extends Fixture implements FixtureGroupInterface
// , DependentFixtureInterface
{
    private CountryRepository $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function load(ObjectManager $manager)
    {
        $filePath = __DIR__ . '/data/clubs.xlsx';
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $data = [];
            foreach ($cellIterator as $cell) {
                $data[] = $cell->getValue();
            }

            $place = new Place();
            $place->setName($data[0]);
            $place->setAddressStreet($data[1]);

            if (null != $data[2]) {
                $place->setAddressNumber($data[2]);
            }

            $place->setAddressZipcode($data[3]);
            $place->setAddressLocality($data[4]);

            $country = $this->countryRepository->findOneBy(['alpha2' => $data[6]]);
            $place->setAddressCountry($country);

            $manager->persist($place);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['place'];
    }

    // public function getDependencies()
    // {
    //     return [
    //         CountryFixture::class,
    //     ];
    // }
}
