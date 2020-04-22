<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200422110539 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__join_requests AS SELECT id, user_id, chat_id FROM join_requests');
        $this->addSql('DROP TABLE join_requests');
        $this->addSql('CREATE TABLE join_requests (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, chat_id INTEGER NOT NULL)');
        $this->addSql('INSERT INTO join_requests (id, user_id, chat_id) SELECT id, user_id, chat_id FROM __temp__join_requests');
        $this->addSql('DROP TABLE __temp__join_requests');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('ALTER TABLE join_requests ADD COLUMN request_date DATETIME NOT NULL');
    }
}
