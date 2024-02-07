<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FileUploader
{
    private SluggerInterface $slugger;
    private TranslatorInterface $translator;
    private ParameterBagInterface $container;
    private MimeTypesInterface $mimeTypes;

    public function __construct(SluggerInterface $slugger, TranslatorInterface $translator, ParameterBagInterface $container, MimeTypesInterface $mimeTypes)
    {
        $this->slugger = $slugger;
        $this->translator = $translator;
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
            throw new Exception($this->translator->trans('error.file.not_valid'));
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
