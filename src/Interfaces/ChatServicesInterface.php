<?php

namespace App\Interfaces;

interface ChatServicesInterface
{
    public function getCustomName(string $name, array $members, int $currentId): string;
    public function getLatestMessage(object $messages): string;
    public function getLatestMessageDate(object $messages): ?\DateTimeInterface;
    public function getAmountOfUnseenMessages(object $messages, int $currentId): int;
    public function getLastFileId(int $chatId): int;
}
