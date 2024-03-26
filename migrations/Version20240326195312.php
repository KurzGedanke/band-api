<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240326195312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE time_slot (id INT AUTO_INCREMENT NOT NULL, band_id INT DEFAULT NULL, stage_id INT DEFAULT NULL, start_time DATETIME NOT NULL, end_time DATETIME DEFAULT NULL, INDEX IDX_1B3294A49ABEB17 (band_id), INDEX IDX_1B3294A2298D193 (stage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE time_slot ADD CONSTRAINT FK_1B3294A49ABEB17 FOREIGN KEY (band_id) REFERENCES band (id)');
        $this->addSql('ALTER TABLE time_slot ADD CONSTRAINT FK_1B3294A2298D193 FOREIGN KEY (stage_id) REFERENCES stage (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE time_slot DROP FOREIGN KEY FK_1B3294A49ABEB17');
        $this->addSql('ALTER TABLE time_slot DROP FOREIGN KEY FK_1B3294A2298D193');
        $this->addSql('DROP TABLE time_slot');
    }
}
