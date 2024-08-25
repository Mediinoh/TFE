<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240824164703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_achat DROP FOREIGN KEY FK_68295E25F77D927C');
        $this->addSql('DROP INDEX IDX_68295E25F77D927C ON historique_achat');
        $this->addSql('ALTER TABLE historique_achat DROP panier_id, CHANGE utilisateur_id utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B3E4DA9E5 FOREIGN KEY (historique_achat_id) REFERENCES historique_achat (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3170B74B3E4DA9E5 ON ligne_commande (historique_achat_id)');
        $this->addSql('ALTER TABLE panier DROP is_active');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B3E4DA9E5');
        $this->addSql('DROP INDEX IDX_3170B74B3E4DA9E5 ON ligne_commande');
        $this->addSql('ALTER TABLE panier ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE historique_achat ADD panier_id INT DEFAULT NULL, CHANGE utilisateur_id utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE historique_achat ADD CONSTRAINT FK_68295E25F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_68295E25F77D927C ON historique_achat (panier_id)');
    }
}
