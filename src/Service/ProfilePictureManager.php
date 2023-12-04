<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProfilePictureManager
{
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function handleProfilePicture($form, $user): void
    {
        $picture = $form->get('profile_picture')->getData();

        if ($picture) {
            try {
                $pictureFileName = $this->fileUploader->save($picture, 'pictures_directory');
                $user->setProfilePicture($pictureFileName);
            } catch (FileException $e) {
                // Handle exception or log it
                // For example: $this->logger->error('Error saving profile picture: ' . $e->getMessage());
            }
        }
    }
}
