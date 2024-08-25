<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240822133636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire CHANGE date_commentaire date_commentaire DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE historique_achat CHANGE utilisateur_id utilisateur_id INT DEFAULT NULL, CHANGE panier_id panier_id INT DEFAULT NULL, CHANGE date_achat date_achat DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE historique_connexion CHANGE date_connexion date_connexion DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE message CHANGE date_message date_message DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE panier ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_7831F9F47294869C');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_7831F9F4FB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_E3F1A90BFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_E3F1A90B7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE favoris_utilisateur RENAME INDEX idx_7831f9f4fb88e14f TO IDX_E3F1A90BFB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur RENAME INDEX idx_7831f9f47294869c TO IDX_E3F1A90B7294869C');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_connexion CHANGE date_connexion date_connexion DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE panier DROP is_active');
        $this->addSql('ALTER TABLE commentaire CHANGE date_commentaire date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_E3F1A90BFB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_E3F1A90B7294869C');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_7831F9F47294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_7831F9F4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_utilisateur RENAME INDEX idx_e3f1a90bfb88e14f TO IDX_7831F9F4FB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur RENAME INDEX idx_e3f1a90b7294869c TO IDX_7831F9F47294869C');
        $this->addSql('ALTER TABLE historique_achat CHANGE utilisateur_id utilisateur_id INT NOT NULL, CHANGE panier_id panier_id INT NOT NULL, CHANGE date_achat date_achat DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE message CHANGE date_message date_message DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
