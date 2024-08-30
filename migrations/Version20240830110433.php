<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240830110433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixes JSON conversion and adjusts timestamp columns.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE belongs_to ALTER COLUMN team_roles TYPE JSONB USING team_roles::JSONB');
        $this->addSql('ALTER TABLE belongs_to ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE belongs_to ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN belongs_to.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN belongs_to.updated_at IS \'(DC2Type:datetime_immutable)\'');
        
        $this->addSql('ALTER TABLE category ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE category ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN category.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN category.updated_at IS \'(DC2Type:datetime_immutable)\'');
        
        $this->addSql('ALTER TABLE task ALTER COLUMN accept_deadline TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN completion_deadline TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN datetime_accepted TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN datetime_completed TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN task.accept_deadline IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task.completion_deadline IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task.datetime_accepted IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task.datetime_completed IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task.updated_at IS \'(DC2Type:datetime_immutable)\'');
        
        $this->addSql('ALTER TABLE team ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE team ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN team.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN team.updated_at IS \'(DC2Type:datetime_immutable)\'');
        
        $this->addSql('ALTER TABLE "user" ALTER COLUMN roles TYPE JSONB USING roles::JSONB');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE belongs_to ALTER COLUMN team_roles TYPE TEXT');
        $this->addSql('ALTER TABLE belongs_to ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE belongs_to ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        
        $this->addSql('ALTER TABLE category ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE category ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        
        $this->addSql('ALTER TABLE task ALTER COLUMN accept_deadline TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN completion_deadline TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN datetime_accepted TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN datetime_completed TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE task ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        
        $this->addSql('ALTER TABLE team ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE team ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        
        $this->addSql('ALTER TABLE "user" ALTER COLUMN roles TYPE TEXT');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }
}
