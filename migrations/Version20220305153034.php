<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220305153034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfert_bdd (id INT AUTO_INCREMENT NOT NULL, idreservation INT DEFAULT NULL, client INT DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, contact VARCHAR(255) DEFAULT NULL, date DATE DEFAULT NULL, datef DATE DEFAULT NULL, place INT DEFAULT NULL, code INT DEFAULT NULL, date_reservation DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE reservation_old');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_old (idreservation INT AUTO_INCREMENT NOT NULL, id INT NOT NULL, nom TEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, contact TEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, date DATE NOT NULL, datef DATE NOT NULL, place INT NOT NULL, code INT NOT NULL, date_reservation DATE DEFAULT \'CURRENT_TIMESTAMP\', PRIMARY KEY(idreservation)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE transfert_bdd');
    }
}
