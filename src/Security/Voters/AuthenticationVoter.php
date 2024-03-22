<?php

namespace App\Security\Voters;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AuthenticationVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const TOGGLE = 'toggle';


    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::TOGGLE])) {
            return false;
        }

        // only vote on `Task` objects
        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Task $task */
        $task = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($task, $user),
            self::EDIT => $this->canEdit($task, $user),
            self::DELETE => $this->canDelete($task, $user),
            self::TOGGLE => $this->canToggle($task, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canEdit(Task $task, User $user): bool
    {
        return $user === $task->getAuthor();
    }
    private function canDelete(Task $task, User $user): bool
    {
        return ($user === $task->getAuthor()) || ($user->hasRole('ROLE_ADMIN') && $task->getAuthor()->isAnonymous());
    }

    private function canView(Task $task, User $user): bool
    {
        return true;
    }

    private function canToggle(Task $task, User $user): bool
    {
        return true;
    }
}