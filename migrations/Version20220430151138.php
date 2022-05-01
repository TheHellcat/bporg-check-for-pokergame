<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220430151138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE config (config_key VARCHAR(128) NOT NULL, value VARCHAR(256) NOT NULL, PRIMARY KEY(config_key)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messages (entry_id VARCHAR(64) NOT NULL, bporg_id VARCHAR(64) NOT NULL, message LONGTEXT NOT NULL, cent_value INT NOT NULL, timestamp INT NOT NULL, INDEX idx_bporgid (bporg_id), PRIMARY KEY(entry_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player_inventory (entry_id VARCHAR(64) NOT NULL, player VARCHAR(128) NOT NULL, item_id VARCHAR(64) NOT NULL, amount INT NOT NULL, timestamp INT NOT NULL, PRIMARY KEY(entry_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE config');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE player_inventory');
    }
}
