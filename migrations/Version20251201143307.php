<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251201143307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE billing_document (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, original_filename VARCHAR(255) NOT NULL, stored_filename VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, for_subcontractor TINYINT(1) DEFAULT 0 NOT NULL, sent_at DATETIME DEFAULT NULL, viewed_at DATETIME DEFAULT NULL, client_id INT NOT NULL, INDEX IDX_C0B5E38A19EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, subject VARCHAR(150) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE maintenance_contract (id INT AUTO_INCREMENT NOT NULL, price_per_month INT NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, client_id INT NOT NULL, plan_id INT NOT NULL, INDEX IDX_F7F72C7419EB6921 (client_id), INDEX IDX_F7F72C74E899029B (plan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE maintenance_plan (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(80) NOT NULL, description LONGTEXT DEFAULT NULL, price_per_month INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, firstname VARCHAR(100) DEFAULT NULL, lastname VARCHAR(100) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE billing_document ADD CONSTRAINT FK_C0B5E38A19EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE maintenance_contract ADD CONSTRAINT FK_F7F72C7419EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE maintenance_contract ADD CONSTRAINT FK_F7F72C74E899029B FOREIGN KEY (plan_id) REFERENCES maintenance_plan (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billing_document DROP FOREIGN KEY FK_C0B5E38A19EB6921');
        $this->addSql('ALTER TABLE maintenance_contract DROP FOREIGN KEY FK_F7F72C7419EB6921');
        $this->addSql('ALTER TABLE maintenance_contract DROP FOREIGN KEY FK_F7F72C74E899029B');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE billing_document');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE maintenance_contract');
        $this->addSql('DROP TABLE maintenance_plan');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
