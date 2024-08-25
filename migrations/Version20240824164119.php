<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240824164119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add historique_achat_id to ligne_commande and setup constraints and index';
    }

    public function up(Schema $schema): void
    {
        // Add the column if it doesn't exist
        $this->addSql('
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = "ligne_commande"
                AND COLUMN_NAME = "historique_achat_id"
            )
        ');

        $this->addSql('
            IF @column_exists = 0 THEN
                ALTER TABLE ligne_commande ADD historique_achat_id INT NOT NULL
            END IF
        ');

        // Add the foreign key constraint
        $this->addSql('
            SET @constraint_exists = (
                SELECT COUNT(*)
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = "ligne_commande"
                AND CONSTRAINT_NAME = "FK_3170B74B3E4DA9E5"
            )
        ');

        $this->addSql('
            IF @constraint_exists = 0 THEN
                ALTER TABLE ligne_commande 
                ADD CONSTRAINT FK_3170B74B3E4DA9E5 
                FOREIGN KEY (historique_achat_id) 
                REFERENCES historique_achat (id) 
                ON DELETE CASCADE
            END IF
        ');

        // Create the index if it doesn't exist
        $this->addSql('
            SET @index_exists = (
                SELECT COUNT(*)
                FROM information_schema.STATISTICS
                WHERE TABLE_NAME = "ligne_commande"
                AND INDEX_NAME = "IDX_3170B74B3E4DA9E5"
            )
        ');

        $this->addSql('
            IF @index_exists = 0 THEN
                CREATE INDEX IDX_3170B74B3E4DA9E5 
                ON ligne_commande (historique_achat_id)
            END IF
        ');

        // Drop the 'is_active' column from the 'panier' table
        $this->addSql('ALTER TABLE panier DROP COLUMN IF EXISTS is_active');
    }

    public function down(Schema $schema): void
    {
        // Drop the foreign key constraint if it exists
        $this->addSql('
            SET @constraint_exists = (
                SELECT COUNT(*)
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = "ligne_commande"
                AND CONSTRAINT_NAME = "FK_3170B74B3E4DA9E5"
            )
        ');

        $this->addSql('
            IF @constraint_exists THEN
                ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B3E4DA9E5
            END IF
        ');

        // Drop the index if it exists
        $this->addSql('
            SET @index_exists = (
                SELECT COUNT(*)
                FROM information_schema.STATISTICS
                WHERE TABLE_NAME = "ligne_commande"
                AND INDEX_NAME = "IDX_3170B74B3E4DA9E5"
            )
        ');

        $this->addSql('
            IF @index_exists THEN
                DROP INDEX IDX_3170B74B3E4DA9E5 ON ligne_commande
            END IF
        ');

        // Drop the column if it exists
        $this->addSql('
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = "ligne_commande"
                AND COLUMN_NAME = "historique_achat_id"
            )
        ');

        $this->addSql('
            IF @column_exists THEN
                ALTER TABLE ligne_commande DROP COLUMN historique_achat_id
            END IF
        ');

        // Re-add the 'is_active' column to the 'panier' table
        $this->addSql('ALTER TABLE panier ADD COLUMN is_active TINYINT(1) NOT NULL');
    }
}
