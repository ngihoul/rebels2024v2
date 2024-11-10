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
        $parent = $token->getUser();

        if (!$parent instanceof User) {
            return false;
        }

        return $this->canSwitch($parent, $subject);
    }

    private function canSwitch(User $parent, User $child): bool
    {
        if (!in_array('ROLE_PARENT', $parent->getRoles())) {
            return false;
        }

        $children = $parent->getChildren()->toArray();

        foreach ($children as $c) {
            if ($c->getId() === $child->getId()) {
                return true;
            }
        }

        return false;
    }
}
