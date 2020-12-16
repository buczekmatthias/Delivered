<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201214130642 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chats (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, members CLOB NOT NULL --(DC2Type:array)
        , files CLOB DEFAULT NULL --(DC2Type:array)
        )');
        $this->addSql('CREATE TABLE invitations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER NOT NULL, to_who_id INTEGER NOT NULL, requested_at DATETIME NOT NULL, status BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_232710AEF624B39D ON invitations (sender_id)');
        $this->addSql('CREATE INDEX IDX_232710AED23057BC ON invitations (to_who_id)');
        $this->addSql('CREATE TABLE messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER NOT NULL, chat_id INTEGER NOT NULL, content CLOB NOT NULL --(DC2Type:array)
        , send_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_DB021E96F624B39D ON messages (sender_id)');
        $this->addSql('CREATE INDEX IDX_DB021E961A9A7125 ON messages (chat_id)');
        $this->addSql('CREATE TABLE requests (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, chat_id INTEGER NOT NULL, by_who_id INTEGER NOT NULL, requested_at DATETIME NOT NULL, status BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_7B85D6511A9A7125 ON requests (chat_id)');
        $this->addSql('CREATE INDEX IDX_7B85D651B73E54EB ON requests (by_who_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, login VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, email VARCHAR(300) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AA08CB10 ON user (login)');
        $this->addSql('CREATE TABLE user_details (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, friends_id INTEGER DEFAULT NULL, login VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, email VARCHAR(300) NOT NULL, name CLOB NOT NULL --(DC2Type:array)
        , city VARCHAR(300) DEFAULT NULL, birthday_date DATE DEFAULT NULL, joined_at DATETIME NOT NULL, image VARCHAR(500) DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A2B1580AA08CB10 ON user_details (login)');
        $this->addSql('CREATE INDEX IDX_2A2B158049CA8337 ON user_details (friends_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE chats');
        $this->addSql('DROP TABLE invitations');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE requests');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_details');
    }
}
