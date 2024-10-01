<?php

namespace App\Security\Voter;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SwitchUserVoter extends Voter
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'SWITCH' && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $parent = $token->getUser();

        if (!$parent instanceof User) {
            $this->logger->info('L\'utilisateur courant n\'est pas une instance de User.');
            return false;
        }

        return $this->canSwitch($parent, $subject);
    }

    private function canSwitch(User $parent, User $child): bool
    {
        if (!in_array('ROLE_PARENT', $parent->getRoles())) {
            $this->logger->info('L\'utilisateur courant n\'a pas le rôle ROLE_PARENT.');
            return false;
        }

        $children = $parent->getChildren()->toArray();
        $this->logger->info('Enfants du parent:', ['children' => $children]);

        foreach ($children as $c) {
            $this->logger->info('Enfant fetché depuis le parent:' . $c->getId() . $c->getFirstname());
            $this->logger->info('Enfant passé à canSwitch:' . $child->getId() . $child->getFirstname());
            if ($c->getId() === $child->getId()) {
                $this->logger->info('L\'enfant est trouvé dans la liste des enfants du parent.');
                return true;
            }
        }

        $this->logger->info('L\'enfant n\'est pas dans la liste des enfants du parent.');
        return false;
    }
}
