<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220305161829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfert_bdd ADD admin_id INT NOT NULL, ADD json_filename VARCHAR(255) NOT NULL, DROP idreservation, DROP client, DROP nom, DROP contact, DROP datef, DROP place, DROP code, DROP date_reservation, CHANGE date date DATE NOT NULL');
        $this->addSql('ALTER TABLE transfert_bdd ADD CONSTRAINT FK_3CD3E633642B8210 FOREIGN KEY (admin_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_3CD3E633642B8210 ON transfert_bdd (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfert_bdd DROP FOREIGN KEY FK_3CD3E633642B8210');
        $this->addSql('DROP INDEX IDX_3CD3E633642B8210 ON transfert_bdd');
        $this->addSql('ALTER TABLE transfert_bdd ADD idreservation INT DEFAULT NULL, ADD client INT DEFAULT NULL, ADD nom VARCHAR(255) DEFAULT NULL, ADD contact VARCHAR(255) DEFAULT NULL, ADD datef DATE DEFAULT NULL, ADD place INT DEFAULT NULL, ADD code INT DEFAULT NULL, ADD date_reservation DATE DEFAULT NULL, DROP admin_id, DROP json_filename, CHANGE date date DATE DEFAULT NULL');
    }
}
