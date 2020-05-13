<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200513102831 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE chat_files (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file BLOB NOT NULL, user_id INTEGER NOT NULL, chat_id INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE chats (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, chat_hash VARCHAR(50) NOT NULL, chat_name VARCHAR(255) DEFAULT NULL, image BLOB DEFAULT NULL)');
        $this->addSql('CREATE TABLE chat_members (chats_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(chats_id, user_id))');
        $this->addSql('CREATE TABLE chat_admins (chats_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(chats_id, user_id))');
        $this->addSql('CREATE TABLE join_requests (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, chat_id INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, date DATETIME NOT NULL, sender_id INTEGER NOT NULL, chat_id INTEGER NOT NULL, file_id INTEGER DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DB021E9693CB796C ON messages (file_id)');
        $this->addSql('CREATE TABLE messages_user (messages_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(messages_id, user_id))');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, login VARCHAR(75) NOT NULL, password VARCHAR(75) NOT NULL, email VARCHAR(300) NOT NULL, name VARCHAR(100) NOT NULL, surname VARCHAR(250) NOT NULL, user_img BLOB DEFAULT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE chat_files');
        $this->addSql('DROP TABLE chats');
        $this->addSql('DROP TABLE chat_members');
        $this->addSql('DROP TABLE chat_admins');
        $this->addSql('DROP TABLE join_requests');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE messages_user');
        $this->addSql('DROP TABLE user');
    }
}
