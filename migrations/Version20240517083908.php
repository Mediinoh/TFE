<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517083908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE historique_achat (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, panier_id INT NOT NULL, date_achat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\', montant_total DOUBLE PRECISION NOT NULL, INDEX IDX_68295E25FB88E14F (utilisateur_id), INDEX IDX_68295E25F77D927C (panier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE historique_achat ADD CONSTRAINT FK_68295E25FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE historique_achat ADD CONSTRAINT FK_68295E25F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_achat DROP FOREIGN KEY FK_68295E25FB88E14F');
        $this->addSql('ALTER TABLE historique_achat DROP FOREIGN KEY FK_68295E25F77D927C');
        $this->addSql('DROP TABLE historique_achat');
    }
}
