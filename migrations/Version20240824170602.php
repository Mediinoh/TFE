<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240824170602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_achat DROP FOREIGN KEY FK_68295E25FB88E14F');
        $this->addSql('ALTER TABLE historique_achat ADD CONSTRAINT FK_68295E25FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BF77D927C');
        $this->addSql('DROP INDEX IDX_3170B74BF77D927C ON ligne_commande');
        $this->addSql('ALTER TABLE ligne_commande DROP panier_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_commande ADD panier_id INT NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BF77D927C FOREIGN KEY (panier_id) REFERENCES panier (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3170B74BF77D927C ON ligne_commande (panier_id)');
        $this->addSql('ALTER TABLE historique_achat DROP FOREIGN KEY FK_68295E25FB88E14F');
        $this->addSql('ALTER TABLE historique_achat ADD CONSTRAINT FK_68295E25FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
