<?php

namespace App\Interfaces;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

interface ChatServicesInterface
{
    public function createChat(array $data, object $currentUser, ParameterBagInterface $pb): ?int;
    public function findChatId(int $id, object $currentUser): int;
    public function getCustomName(string $name, array $members, int $currentId): string;
    public function getLatestMessage(object $messages): string;
    public function getLatestMessageDate(object $messages): ?\DateTimeInterface;
    public function getAmountOfUnseenMessages(object $messages, int $currentId): int;
    public function getLastFileId(int $chatId): int;
    public function getChatAmountOfFiles(int $chatId): int;
    public function getChatFiles(object $messages): array;
    public function getChatMembers(object $chat): array;
}
