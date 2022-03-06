<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220306193727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE date_reservation (date_id INT NOT NULL, reservation_id INT NOT NULL, INDEX IDX_7DB17C89B897366B (date_id), INDEX IDX_7DB17C89B83297E7 (reservation_id), PRIMARY KEY(date_id, reservation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE date_reservation ADD CONSTRAINT FK_7DB17C89B897366B FOREIGN KEY (date_id) REFERENCES date (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE date_reservation ADD CONSTRAINT FK_7DB17C89B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE date DROP nombre_place');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955B897366B');
        $this->addSql('DROP INDEX IDX_42C84955B897366B ON reservation');
        $this->addSql('ALTER TABLE reservation DROP date_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE date_reservation');
        $this->addSql('ALTER TABLE date ADD nombre_place INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD date_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955B897366B FOREIGN KEY (date_id) REFERENCES date (id)');
        $this->addSql('CREATE INDEX IDX_42C84955B897366B ON reservation (date_id)');
    }
}
