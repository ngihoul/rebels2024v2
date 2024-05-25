<?php

namespace App\Service;

// Copyrigth Setasign FDPI : https://www.setasign.com/products/fpdi/about/#p-228
use setasign\Fpdi\Fpdi;

class LicensePDFGenerator
{
    const STATUS_ON_DEMAND = 0;

    // Value also define in DB Table LicenceSubCategories
    const SUBCAT_BASEBALL = 1,
        SUBCAT_SOFTBALL = 2,
        SUBCAT_SLOWPITCH = 3,
        SUBCAT_RECREANT = 4,
        SUBCAT_BASEBALL5 = 5,
        SUBCAT_COACH_ASSISTANT = 6,
        SUBCAT_COACH_NIV1_2 = 7,
        SUBCAT_SUPPORTER = 8,
        SUBCAT_ADMINISTRATOR = 9,
        SUBCAT_FEDERAL_UMPIRE = 10,
        SUBCAT_REGIONAL_UMPIRE = 11,
        SUBCAT_FEDERAL_SCORER = 12,
        SUBCAT_REGIONAL_SCORER = 13;


    // Define in service.yml > arguments
    private $pdfPath;
    private $outputPath;

    public function __construct(string $pdfPath, string $outputPath)
    {
        $this->pdfPath = $pdfPath;
        $this->outputPath = $outputPath;
    }

    public function generate($license)
    {
        $outputFileName = $license->getSeason() . '_' . $license->getId() . '_' . $license->getUser()->getLastname() . '.pdf';
        $pdfPath = $this->pdfPath . $license->getSeason() . '.pdf';
        $outputPath = $this->outputPath . $outputFileName;

        $pdf = new Fpdi('l');
        $pdf->AddPage();
        $pdf->setSourceFile($pdfPath);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);

        // New member ?
        if (!$license->getUser()->getLicenseNumber()) {
            $pdf->SetLineWidth(0.5);
            $pdf->Line(50.5, 33, 57.5, 33);
        } else {
            $pdf->SetLineWidth(0.5);
            $pdf->Line(41, 33, 46, 33);

            $pdf->SetXY(120.7, 28.25);
            $pdf->Cell(0, 10, $license->getUser()->getLicenseNumber(), 0, 1);
        }

        // Tick licence categories
        foreach ($license->getSubCategories() as $subCategory) {
            if ($subCategory->getValue() === self::SUBCAT_BASEBALL) {
                $pdf->SetXY(48, 42);
                $pdf->Cell(25, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_SOFTBALL) {
                $pdf->SetXY(103, 42);
                $pdf->Cell(25, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_SLOWPITCH) {
                $pdf->SetXY(36, 46.75);
                $pdf->Cell(20, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_RECREANT) {
                $pdf->SetXY(64.25, 46.75);
                $pdf->Cell(20, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_BASEBALL5) {
                $pdf->SetXY(103, 46.75);
                $pdf->Cell(25, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_COACH_ASSISTANT) {
                $pdf->SetXY(34, 52.75);
                $pdf->Cell(23, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_COACH_NIV1_2) {
                $pdf->SetXY(61.5, 52.75);
                $pdf->Cell(26, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_SUPPORTER) {
                $pdf->SetXY(92.3, 52.75);
                $pdf->Cell(21.75, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_ADMINISTRATOR) {
                $pdf->SetXY(118.5, 52.75);
                $pdf->Cell(21.75, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_FEDERAL_UMPIRE) {
                $pdf->SetXY(34, 58.8);
                $pdf->Cell(23, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_REGIONAL_UMPIRE) {
                $pdf->SetXY(61.5, 58.8);
                $pdf->Cell(26, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_FEDERAL_SCORER) {
                $pdf->SetXY(92.3, 58.8);
                $pdf->Cell(21.75, 4, ' ', 1, 1);
            }

            if ($subCategory->getValue() === self::SUBCAT_REGIONAL_SCORER) {
                $pdf->SetXY(118.5, 58.8);
                $pdf->Cell(21.75, 4, ' ', 1, 1);
            }
        }

        // Fill in the PDF with user data
        $pdf->SetXY(35, 79.25);
        $pdf->Cell(0, 10, 'Liege Rebels Baseball & Softball Club', 0, 1);

        $pdf->SetXY(35, 85);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ASCII//TRANSLIT', $license->getUser()->getLastname()), 0, 1);

        $pdf->SetXY(35, 90.75);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ASCII//TRANSLIT', $license->getUser()->getFirstname()), 0, 1);

        $pdf->SetXY(35, 96.50);
        $pdf->Cell(0, 10, $license->getUser()->getDateOfBirth()->format("d-m-Y"), 0, 1);

        $pdf->SetXY(102.25, 96.5);
        $pdf->Cell(0, 10, $license->getUser()->getGender(), 0, 1);

        $pdf->SetXY(35, 102.25);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ASCII//TRANSLIT', $license->getUser()->getAddressStreet()), 0, 1);

        $pdf->SetXY(118.5, 102.25);
        $pdf->Cell(0, 10, $license->getUser()->getAddressNumber(), 0, 1);

        $pdf->SetXY(35, 108);
        $pdf->Cell(0, 10, $license->getUser()->getZipcode(), 0, 1);

        $pdf->SetXY(96.5, 108);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ASCII//TRANSLIT', $license->getUser()->getLocality()), 0, 1);

        $pdf->SetXY(35, 113.75);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ASCII//TRANSLIT', $license->getUser()->getCountry()->getName()), 0, 1);

        $pdf->SetXY(108, 113.75);
        $pdf->Cell(0, 10, $license->getUser()->getNationality()->getName(), 0, 1);

        $pdf->SetXY(35, 119.5);
        $pdf->Cell(0, 10, $license->getUser()->getPhoneNumber(), 0, 1);

        $pdf->SetXY(92, 119.5);
        $pdf->Cell(0, 10, $license->getUser()->getMobileNumber(), 0, 1);

        $pdf->SetXY(35, 125.25);
        $pdf->Cell(0, 10, $license->getUser()->getEmail(), 0, 1);

        // newsletter_lfbbs
        if ($license->getUser()->isNewsletterLfbbs() === 0) {
            $pdf->SetFillColor(0, 0, 0);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->Rect(13.65, 156.55, 1, 1);
        }

        // Generate PDF
        ob_start();
        // Save PDF on server
        $pdf->Output('F', $outputPath, 'UTF-8');
        $output = ob_get_clean();

        return $outputFileName;
    }
}
