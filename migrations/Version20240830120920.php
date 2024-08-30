<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240830120920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE task ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE team ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE task_id_seq');
        $this->addSql('SELECT setval(\'task_id_seq\', (SELECT MAX(id) FROM task))');
        $this->addSql('ALTER TABLE task ALTER id SET DEFAULT nextval(\'task_id_seq\')');
        $this->addSql('CREATE SEQUENCE category_id_seq');
        $this->addSql('SELECT setval(\'category_id_seq\', (SELECT MAX(id) FROM category))');
        $this->addSql('ALTER TABLE category ALTER id SET DEFAULT nextval(\'category_id_seq\')');
        $this->addSql('CREATE SEQUENCE user_id_seq');
        $this->addSql('SELECT setval(\'user_id_seq\', (SELECT MAX(id) FROM "user"))');
        $this->addSql('ALTER TABLE "user" ALTER id SET DEFAULT nextval(\'user_id_seq\')');
        $this->addSql('CREATE SEQUENCE team_id_seq');
        $this->addSql('SELECT setval(\'team_id_seq\', (SELECT MAX(id) FROM team))');
        $this->addSql('ALTER TABLE team ALTER id SET DEFAULT nextval(\'team_id_seq\')');
    }
}
