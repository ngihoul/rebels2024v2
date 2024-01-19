<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private SluggerInterface $slugger;
    private ParameterBagInterface $container;
    private MimeTypesInterface $mimeTypes;

    public function __construct(SluggerInterface $slugger, ParameterBagInterface $container, MimeTypesInterface $mimeTypes)
    {
        $this->slugger = $slugger;
        $this->container = $container;
        $this->mimeTypes = $mimeTypes;
    }

    /**
     * @param UploadedFile $image
     * @param $directory string Where the file should be stored
     * @return string Safe name of the file
     */
    public function save(UploadedFile $image, string $directory): string
    {
        $allowedImageMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $fileMimeType = $this->mimeTypes->guessMimeType($image->getPathname());

        if (!in_array($fileMimeType, $allowedImageMimeTypes)) {
            throw new \Exception('Le fichier n\'est pas une image valide.');
        }

        $originalFileName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFileName = $this->slugger->slug($originalFileName);
        $newFileName = $safeFileName . '-' . uniqid() . '.' . $image->guessExtension();

        $image->move(
            $this->container->get($directory),
            $newFileName
        );

        return $newFileName;
    }
}
