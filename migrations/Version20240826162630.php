<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240826162630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message_reaction (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, utilisateur_id INT NOT NULL, reaction_type ENUM(\'like\', \'dislike\'), INDEX IDX_ADF1C3E6537A1329 (message_id), INDEX IDX_ADF1C3E6FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message_reaction ADD CONSTRAINT FK_ADF1C3E6537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message_reaction ADD CONSTRAINT FK_ADF1C3E6FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message_reaction DROP FOREIGN KEY FK_ADF1C3E6537A1329');
        $this->addSql('ALTER TABLE message_reaction DROP FOREIGN KEY FK_ADF1C3E6FB88E14F');
        $this->addSql('DROP TABLE message_reaction');
    }
}
