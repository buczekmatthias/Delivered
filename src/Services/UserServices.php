<?php

namespace App\Services;

use App\Entity\User;
use App\Interfaces\UserServicesInterface;

class UserServices implements UserServicesInterface
{
    public function getFriends(object $current): ?array
    {
        $friends = [];

        if ($current instanceof User && $current->getFriends()) {
            foreach ($current->getFriends() as $friend) {
                $friends[] = $friend;
            }
        }

        return empty($friends) ? null : $friends;
    }

    public function getFriendsIds(object $current): ?array
    {
        $friendsIds = [];

        if ($current instanceof User && $this->getFriends($current)) {
            foreach ($this->getFriends($current) as $friend) {
                $friendsIds[] = $friend->getId();
            }
        }

        return $friendsIds;
    }

    public function getActiveFriends(object $current): ?array
    {
        $friends = $this->getFriends($current);

        if (!$friends) {
            return null;
        }

        $activeFriends = [];

        foreach ($friends as $friend) {
            if ($friend->isActive() && !$this->isUserInList($current, $activeFriends)) {
                $activeFriends[] = $friend;
            }
        }

        return $activeFriends;
    }

    public function getUsersFromInvitations(object $current, string $mode = "objects"): ?array
    {
        $invitationsUsers = [];
        if ($mode === "objects") {
            foreach ($current->getSentInvitation() as $sent) {
                $invitationsUsers[] = $sent->getToWho();
            }

            foreach ($current->getReceivedInvitations() as $received) {
                $invitationsUsers[] = $received->getSender();
            }
        } else if ($mode === "ids") {
            foreach ($current->getSentInvitation() as $sent) {
                $invitationsUsers[] = $sent->getToWho()->getId();
            }

            foreach ($current->getReceivedInvitations() as $received) {
                $invitationsUsers[] = $received->getSender()->getId();
            }
        } else {
            return null;
        }

        $invitationsUsers = array_unique($invitationsUsers);

        return $invitationsUsers;
    }

    public function isUserInList(object $user, array $list): bool
    {
        foreach ($list as $row) {
            foreach ($row as $el) {
                if ($el->getId() === $user->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function checkIfUserInFriends(object $current, int $userId): bool
    {
        $friends = $this->getFriends($current);

        if ($friends) {
            return false;
        }

        foreach ($friends as $friend) {
            if ($friend->getId() === $userId) {
                return true;
            }
        }

        return false;
    }

    public function getUsersToAddToChat(object $current, array $members = null): ?array
    {
        $friends = $this->getFriends($current);

        if ($members === null) {
            return $friends;
        }

        $users = [];

        foreach ($friends as $friend) {
            if (!$this->isUserInList($friend, $members)) {
                $users[] = $friend;
            }
        }

        return $users;
    }
}
