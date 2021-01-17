<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201215170302 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_232710AEF624B39D');
        $this->addSql('DROP INDEX IDX_232710AED23057BC');
        $this->addSql('CREATE TEMPORARY TABLE __temp__invitations AS SELECT id, sender_id, to_who_id, requested_at, status FROM invitations');
        $this->addSql('DROP TABLE invitations');
        $this->addSql('CREATE TABLE invitations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER NOT NULL, to_who_id INTEGER NOT NULL, requested_at DATETIME NOT NULL, status BOOLEAN NOT NULL, seen BOOLEAN NOT NULL, CONSTRAINT FK_232710AEF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_232710AED23057BC FOREIGN KEY (to_who_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO invitations (id, sender_id, to_who_id, requested_at, status) SELECT id, sender_id, to_who_id, requested_at, status FROM __temp__invitations');
        $this->addSql('DROP TABLE __temp__invitations');
        $this->addSql('CREATE INDEX IDX_232710AEF624B39D ON invitations (sender_id)');
        $this->addSql('CREATE INDEX IDX_232710AED23057BC ON invitations (to_who_id)');
        $this->addSql('DROP INDEX IDX_DB021E96F624B39D');
        $this->addSql('DROP INDEX IDX_DB021E961A9A7125');
        $this->addSql('CREATE TEMPORARY TABLE __temp__messages AS SELECT id, sender_id, chat_id, content, send_at FROM messages');
        $this->addSql('DROP TABLE messages');
        $this->addSql('CREATE TABLE messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER NOT NULL, chat_id INTEGER NOT NULL, content CLOB NOT NULL COLLATE BINARY --(DC2Type:array)
        , send_at DATETIME NOT NULL, CONSTRAINT FK_DB021E96F624B39D FOREIGN KEY (sender_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DB021E961A9A7125 FOREIGN KEY (chat_id) REFERENCES chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO messages (id, sender_id, chat_id, content, send_at) SELECT id, sender_id, chat_id, content, send_at FROM __temp__messages');
        $this->addSql('DROP TABLE __temp__messages');
        $this->addSql('CREATE INDEX IDX_DB021E96F624B39D ON messages (sender_id)');
        $this->addSql('CREATE INDEX IDX_DB021E961A9A7125 ON messages (chat_id)');
        $this->addSql('DROP INDEX IDX_6000B0D3D23057BC');
        $this->addSql('CREATE TEMPORARY TABLE __temp__notifications AS SELECT id, to_who_id, content, received_at, seen FROM notifications');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('CREATE TABLE notifications (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, to_who_id INTEGER NOT NULL, content CLOB NOT NULL COLLATE BINARY, received_at DATETIME NOT NULL, seen BOOLEAN NOT NULL, CONSTRAINT FK_6000B0D3D23057BC FOREIGN KEY (to_who_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO notifications (id, to_who_id, content, received_at, seen) SELECT id, to_who_id, content, received_at, seen FROM __temp__notifications');
        $this->addSql('DROP TABLE __temp__notifications');
        $this->addSql('CREATE INDEX IDX_6000B0D3D23057BC ON notifications (to_who_id)');
        $this->addSql('DROP INDEX IDX_7B85D6511A9A7125');
        $this->addSql('DROP INDEX IDX_7B85D651B73E54EB');
        $this->addSql('CREATE TEMPORARY TABLE __temp__requests AS SELECT id, chat_id, by_who_id, requested_at, status FROM requests');
        $this->addSql('DROP TABLE requests');
        $this->addSql('CREATE TABLE requests (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, chat_id INTEGER NOT NULL, by_who_id INTEGER NOT NULL, requested_at DATETIME NOT NULL, status BOOLEAN NOT NULL, CONSTRAINT FK_7B85D6511A9A7125 FOREIGN KEY (chat_id) REFERENCES chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7B85D651B73E54EB FOREIGN KEY (by_who_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO requests (id, chat_id, by_who_id, requested_at, status) SELECT id, chat_id, by_who_id, requested_at, status FROM __temp__requests');
        $this->addSql('DROP TABLE __temp__requests');
        $this->addSql('CREATE INDEX IDX_7B85D6511A9A7125 ON requests (chat_id)');
        $this->addSql('CREATE INDEX IDX_7B85D651B73E54EB ON requests (by_who_id)');
        $this->addSql('DROP INDEX IDX_8D93D64949CA8337');
        $this->addSql('DROP INDEX UNIQ_8D93D649AA08CB10');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, friends_id, login, roles, password, email, name, city, birthday_date, joined_at, image, last_activity FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, friends_id INTEGER DEFAULT NULL, login VARCHAR(180) NOT NULL COLLATE BINARY, roles CLOB NOT NULL COLLATE BINARY --(DC2Type:json)
        , password VARCHAR(255) NOT NULL COLLATE BINARY, email VARCHAR(300) NOT NULL COLLATE BINARY, name CLOB NOT NULL COLLATE BINARY --(DC2Type:array)
        , city VARCHAR(300) DEFAULT NULL COLLATE BINARY, birthday_date DATE DEFAULT NULL, joined_at DATETIME NOT NULL, image VARCHAR(500) DEFAULT NULL COLLATE BINARY, last_activity DATETIME DEFAULT NULL, CONSTRAINT FK_8D93D64949CA8337 FOREIGN KEY (friends_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user (id, friends_id, login, roles, password, email, name, city, birthday_date, joined_at, image, last_activity) SELECT id, friends_id, login, roles, password, email, name, city, birthday_date, joined_at, image, last_activity FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE INDEX IDX_8D93D64949CA8337 ON user (friends_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AA08CB10 ON user (login)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_232710AEF624B39D');
        $this->addSql('DROP INDEX IDX_232710AED23057BC');
        $this->addSql('CREATE TEMPORARY TABLE __temp__invitations AS SELECT id, sender_id, to_who_id, requested_at, status FROM invitations');
        $this->addSql('DROP TABLE invitations');
        $this->addSql('CREATE TABLE invitations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER NOT NULL, to_who_id INTEGER NOT NULL, requested_at DATETIME NOT NULL, status BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO invitations (id, sender_id, to_who_id, requested_at, status) SELECT id, sender_id, to_who_id, requested_at, status FROM __temp__invitations');
        $this->addSql('DROP TABLE __temp__invitations');
        $this->addSql('CREATE INDEX IDX_232710AEF624B39D ON invitations (sender_id)');
        $this->addSql('CREATE INDEX IDX_232710AED23057BC ON invitations (to_who_id)');
        $this->addSql('DROP INDEX IDX_DB021E96F624B39D');
        $this->addSql('DROP INDEX IDX_DB021E961A9A7125');
        $this->addSql('CREATE TEMPORARY TABLE __temp__messages AS SELECT id, sender_id, chat_id, content, send_at FROM messages');
        $this->addSql('DROP TABLE messages');
        $this->addSql('CREATE TABLE messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sender_id INTEGER NOT NULL, chat_id INTEGER NOT NULL, content CLOB NOT NULL --(DC2Type:array)
        , send_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO messages (id, sender_id, chat_id, content, send_at) SELECT id, sender_id, chat_id, content, send_at FROM __temp__messages');
        $this->addSql('DROP TABLE __temp__messages');
        $this->addSql('CREATE INDEX IDX_DB021E96F624B39D ON messages (sender_id)');
        $this->addSql('CREATE INDEX IDX_DB021E961A9A7125 ON messages (chat_id)');
        $this->addSql('DROP INDEX IDX_6000B0D3D23057BC');
        $this->addSql('CREATE TEMPORARY TABLE __temp__notifications AS SELECT id, to_who_id, content, received_at, seen FROM notifications');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('CREATE TABLE notifications (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, to_who_id INTEGER NOT NULL, content CLOB NOT NULL, received_at DATETIME NOT NULL, seen BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO notifications (id, to_who_id, content, received_at, seen) SELECT id, to_who_id, content, received_at, seen FROM __temp__notifications');
        $this->addSql('DROP TABLE __temp__notifications');
        $this->addSql('CREATE INDEX IDX_6000B0D3D23057BC ON notifications (to_who_id)');
        $this->addSql('DROP INDEX IDX_7B85D6511A9A7125');
        $this->addSql('DROP INDEX IDX_7B85D651B73E54EB');
        $this->addSql('CREATE TEMPORARY TABLE __temp__requests AS SELECT id, chat_id, by_who_id, requested_at, status FROM requests');
        $this->addSql('DROP TABLE requests');
        $this->addSql('CREATE TABLE requests (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, chat_id INTEGER NOT NULL, by_who_id INTEGER NOT NULL, requested_at DATETIME NOT NULL, status BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO requests (id, chat_id, by_who_id, requested_at, status) SELECT id, chat_id, by_who_id, requested_at, status FROM __temp__requests');
        $this->addSql('DROP TABLE __temp__requests');
        $this->addSql('CREATE INDEX IDX_7B85D6511A9A7125 ON requests (chat_id)');
        $this->addSql('CREATE INDEX IDX_7B85D651B73E54EB ON requests (by_who_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649AA08CB10');
        $this->addSql('DROP INDEX IDX_8D93D64949CA8337');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, friends_id, login, roles, password, email, name, city, birthday_date, joined_at, image, last_activity FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, friends_id INTEGER DEFAULT NULL, login VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, email VARCHAR(300) NOT NULL, name CLOB NOT NULL --(DC2Type:array)
        , city VARCHAR(300) DEFAULT NULL, birthday_date DATE DEFAULT NULL, joined_at DATETIME NOT NULL, image VARCHAR(500) DEFAULT NULL, last_activity DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO user (id, friends_id, login, roles, password, email, name, city, birthday_date, joined_at, image, last_activity) SELECT id, friends_id, login, roles, password, email, name, city, birthday_date, joined_at, image, last_activity FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AA08CB10 ON user (login)');
        $this->addSql('CREATE INDEX IDX_8D93D64949CA8337 ON user (friends_id)');
    }
}