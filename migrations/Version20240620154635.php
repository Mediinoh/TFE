<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620154635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favoris_utilisateur (utilisateur_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_7831F9F4FB88E14F (utilisateur_id), INDEX IDX_7831F9F47294869C (article_id), PRIMARY KEY(utilisateur_id, article_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_7831F9F4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_utilisateur ADD CONSTRAINT FK_7831F9F47294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_7831F9F4FB88E14F');
        $this->addSql('ALTER TABLE favoris_utilisateur DROP FOREIGN KEY FK_7831F9F47294869C');
        $this->addSql('DROP TABLE favoris_utilisateur');
    }
}
