<?php
// src/Service/ProfileChecker.php
namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileChecker
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function checkProfileCompletion($user)
    {
        $missingFields = $user->isProfileComplete();
        if (!empty($missingFields)) {
            $translatedFields = array_map(function ($field) {
                return $this->translator->trans('profile.' . $field);
            }, $missingFields);

            $missingFieldsString = implode(', ', $translatedFields);
            $errorMessage = $this->translator->trans('error.license.profile_incomplete') . $missingFieldsString;
            return $errorMessage;
        }

        return null;
    }
}
