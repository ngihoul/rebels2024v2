<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SwitchUserVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'SWITCH' && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Vérifie si l'utilisateur courant est le parent de l'enfant (subject)
        return $this->canSwitch($user, $subject);
    }

    private function canSwitch(User $parent, User $enfant)
    {
        return in_array($enfant, $parent->getChildren()->toArray()); // Exemple si la relation parent-enfant existe
    }
}
