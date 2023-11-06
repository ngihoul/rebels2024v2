<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private SluggerInterface $slugger;
    private ParameterBagInterface $container;

    public function __construct(SluggerInterface $slugger, ParameterBagInterface $container)
    {
        $this->slugger = $slugger;
        $this->container = $container;
    }

    /**
     * @param UploadedFile $image
     * @param $directory string Where the file should be stored
     * @return string Safe name of the file
     */
    public function save(UploadedFile $image, string $directory): string
    {
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
