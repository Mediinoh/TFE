<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240824224320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B3E4DA9E5');
        $this->addSql('DROP INDEX IDX_3170B74B3E4DA9E5 ON ligne_commande');
        $this->addSql('ALTER TABLE ligne_commande CHANGE historique_achat_id utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_3170B74BFB88E14F ON ligne_commande (utilisateur_id)');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_E3F1A90B7294869C');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_E3F1A90BFB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_E3F1A90B7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_E3F1A90BFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BFB88E14F');
        $this->addSql('DROP INDEX IDX_3170B74BFB88E14F ON ligne_commande');
        $this->addSql('ALTER TABLE ligne_commande CHANGE utilisateur_id historique_achat_id INT NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B3E4DA9E5 FOREIGN KEY (historique_achat_id) REFERENCES historique_achat (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3170B74B3E4DA9E5 ON ligne_commande (historique_achat_id)');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_E3F1A90BFB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_E3F1A90B7294869C');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_E3F1A90BFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_E3F1A90B7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
