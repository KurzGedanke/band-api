<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Split the single band `description` into per-language columns.
 *
 * German (`description_de`) is the default language; English (`description_en`)
 * is optional. The legacy `description` column held English copy, so it is
 * renamed to `description_en` to preserve existing data; `description_de` is
 * added empty and populated by `app:seed:band-details`.
 */
final class Version20260623120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Split band.description into description_de (default) and description_en';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE band CHANGE description description_en LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE band ADD description_de LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE band DROP description_de');
        $this->addSql('ALTER TABLE band CHANGE description_en description VARCHAR(255) DEFAULT NULL');
    }
}
