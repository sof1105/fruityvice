<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230325131354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fruits (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, origid DOUBLE PRECISION DEFAULT NULL, family VARCHAR(255) DEFAULT NULL, genus VARCHAR(255) DEFAULT NULL, origorder VARCHAR(255) DEFAULT NULL, carbohydrates DOUBLE PRECISION DEFAULT NULL, protein DOUBLE PRECISION DEFAULT NULL, fat DOUBLE PRECISION DEFAULT NULL, calories DOUBLE PRECISION DEFAULT NULL, sugar DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_75C5C23E3E24BF00 (origid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE fruits');
    }
}