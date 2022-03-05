<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220305171834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfert_bdd DROP FOREIGN KEY FK_3CD3E633642B8210');
        $this->addSql('DROP INDEX IDX_3CD3E633642B8210 ON transfert_bdd');
        $this->addSql('ALTER TABLE transfert_bdd CHANGE admin_id relation_id INT NOT NULL');
        $this->addSql('ALTER TABLE transfert_bdd ADD CONSTRAINT FK_3CD3E6333256915B FOREIGN KEY (relation_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_3CD3E6333256915B ON transfert_bdd (relation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfert_bdd DROP FOREIGN KEY FK_3CD3E6333256915B');
        $this->addSql('DROP INDEX IDX_3CD3E6333256915B ON transfert_bdd');
        $this->addSql('ALTER TABLE transfert_bdd CHANGE relation_id admin_id INT NOT NULL');
        $this->addSql('ALTER TABLE transfert_bdd ADD CONSTRAINT FK_3CD3E633642B8210 FOREIGN KEY (admin_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_3CD3E633642B8210 ON transfert_bdd (admin_id)');
    }
}
