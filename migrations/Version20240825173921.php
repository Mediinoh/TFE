<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240825173921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCFB88E14F');
        $this->addSql('ALTER TABLE commentaire CHANGE date_commentaire date_commentaire DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE historique_achat DROP FOREIGN KEY FK_68295E25FB88E14F');
        $this->addSql('ALTER TABLE historique_achat CHANGE utilisateur_id utilisateur_id INT DEFAULT NULL, CHANGE panier_id panier_id INT DEFAULT NULL, CHANGE date_achat date_achat DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE historique_achat ADD CONSTRAINT FK_68295E25FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE historique_connexion DROP FOREIGN KEY FK_C018B2D4FB88E14F');
        $this->addSql('ALTER TABLE historique_connexion CHANGE date_connexion date_connexion DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE historique_connexion ADD CONSTRAINT FK_C018B2D4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BF77D927C');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BF77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F7B51A1B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FFB88E14F');
        $this->addSql('ALTER TABLE message CHANGE date_message date_message DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7B51A1B FOREIGN KEY (reponse_a_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE message RENAME INDEX fk_b6bd307f7b51a1b TO IDX_B6BD307F7B51A1B');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2FB88E14F');
        $this->addSql('ALTER TABLE panier CHANGE date_achat date_achat DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE montant_total montant_total DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_E3F1A90BFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_E3F1A90B7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE favoris_utilisateur RENAME INDEX idx_7831f9f4fb88e14f TO IDX_E3F1A90BFB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur RENAME INDEX idx_7831f9f47294869c TO IDX_E3F1A90B7294869C');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_connexion DROP FOREIGN KEY FK_C018B2D4FB88E14F');
        $this->addSql('ALTER TABLE historique_connexion CHANGE date_connexion date_connexion DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE historique_connexion ADD CONSTRAINT FK_C018B2D4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FFB88E14F');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F7B51A1B');
        $this->addSql('ALTER TABLE message CHANGE date_message date_message DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7B51A1B FOREIGN KEY (reponse_a_id) REFERENCES message (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE message RENAME INDEX idx_b6bd307f7b51a1b TO FK_B6BD307F7B51A1B');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BF77D927C');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BF77D927C FOREIGN KEY (panier_id) REFERENCES panier (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2FB88E14F');
        $this->addSql('ALTER TABLE panier CHANGE date_achat date_achat DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE montant_total montant_total DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_E3F1A90BFB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_E3F1A90B7294869C');
        $this->addSql('ALTER TABLE favoris_utilisateur RENAME INDEX idx_e3f1a90b7294869c TO IDX_7831F9F47294869C');
        $this->addSql('ALTER TABLE favoris_utilisateur RENAME INDEX idx_e3f1a90bfb88e14f TO IDX_7831F9F4FB88E14F');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCFB88E14F');
        $this->addSql('ALTER TABLE commentaire CHANGE date_commentaire date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE historique_achat DROP FOREIGN KEY FK_68295E25FB88E14F');
        $this->addSql('ALTER TABLE historique_achat CHANGE utilisateur_id utilisateur_id INT NOT NULL, CHANGE panier_id panier_id INT NOT NULL, CHANGE date_achat date_achat DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE historique_achat ADD CONSTRAINT FK_68295E25FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
