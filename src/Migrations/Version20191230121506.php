<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191230121506 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, login, password, email, name, surname FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, login VARCHAR(75) NOT NULL COLLATE BINARY, password VARCHAR(75) NOT NULL COLLATE BINARY, email VARCHAR(300) NOT NULL COLLATE BINARY, surname VARCHAR(250) NOT NULL COLLATE BINARY, name VARCHAR(100) NOT NULL)');
        $this->addSql('INSERT INTO user (id, login, password, email, name, surname) SELECT id, login, password, email, name, surname FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, login, password, email, name, surname FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, login VARCHAR(75) NOT NULL, password VARCHAR(75) NOT NULL, email VARCHAR(300) NOT NULL, surname VARCHAR(250) NOT NULL, name VARCHAR(300) NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO user (id, login, password, email, name, surname) SELECT id, login, password, email, name, surname FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }
}
