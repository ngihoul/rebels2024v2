<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfilePictureManager
{
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function handleProfilePicture($form, $user): void
    {
        $picture = $form->get('profile_picture')->getData() ?? $user->getProfilePicture() ?? null;

        if ($picture instanceof UploadedFile) {
            $this->saveProfilePicture($picture, $user);
        } else if (!$picture) {
            $this->setDefaultProfilePicture($user);
        }
    }

    private function saveProfilePicture($picture, $user): void
    {
        try {
            $pictureFileName = $this->fileUploader->save($picture, 'pictures_directory');
            $user->setProfilePicture($pictureFileName);
        } catch (FileException $e) {
            // Handle exception or log it
            // For example: $this->logger->error('Error saving profile picture: ' . $e->getMessage());
        }
    }

    private function setDefaultProfilePicture($user): void
    {
        $userGender = $user->getGender();
        $userAge = $user->getAge();

        if ($userAge < 16) {
            $user->setProfilePicture('default/default-academy.png');
        } else if ($userGender == "F") {
            $user->setProfilePicture('default/default-softball.png');
        } else if ($userGender == "M") {
            $user->setProfilePicture('default/default-baseball.png');
        }
    }
}
