<?php

namespace App\Interfaces;

interface UserServicesInterface
{
    public function getFriends(object $current): ?array;

    public function getFriendsIds(object $current): ?array;

    public function getActiveFriends(object $current): ?array;

    public function getUsersFromInvitations(object $current, string $mode = "objects"): ?array;

    public function isUserInList(object $user, array $list): bool;

    public function checkIfUserInFriends(object $current, int $userId): bool;

    public function getUsersToAddToChat(object $current, array $members = null): ?array;
}
