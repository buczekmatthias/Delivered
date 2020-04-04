<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200403123922 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__chats AS SELECT id, chat_hash, chat_name FROM chats');
        $this->addSql('DROP TABLE chats');
        $this->addSql('CREATE TABLE chats (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, chat_hash VARCHAR(50) NOT NULL COLLATE BINARY, chat_name VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO chats (id, chat_hash, chat_name) SELECT id, chat_hash, chat_name FROM __temp__chats');
        $this->addSql('DROP TABLE __temp__chats');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__chats AS SELECT id, chat_hash, chat_name FROM chats');
        $this->addSql('DROP TABLE chats');
        $this->addSql('CREATE TABLE chats (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, chat_hash VARCHAR(50) NOT NULL, chat_name VARCHAR(255) NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO chats (id, chat_hash, chat_name) SELECT id, chat_hash, chat_name FROM __temp__chats');
        $this->addSql('DROP TABLE __temp__chats');
    }
}
