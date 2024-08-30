<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230908145212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE TABLE belongs_to (user_id INT NOT NULL, team_id INT NOT NULL, team_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', validated TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4B0E929BA76ED395 (user_id), INDEX IDX_4B0E929B296CD8AE (team_id), PRIMARY KEY(user_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_64C19C1296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, team_id INT NOT NULL, assigned_to_id INT DEFAULT NULL, created_by_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, difficulty SMALLINT NOT NULL, accept_deadline DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', completion_deadline DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', datetime_accepted DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', datetime_completed DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', status SMALLINT DEFAULT 0 NOT NULL, rejected TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_527EDB2512469DE2 (category_id), INDEX IDX_527EDB25296CD8AE (team_id), INDEX IDX_527EDB25F4BD7827 (assigned_to_id), INDEX IDX_527EDB25B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('ALTER TABLE belongs_to ADD CONSTRAINT FK_4B0E929BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        // $this->addSql('ALTER TABLE belongs_to ADD CONSTRAINT FK_4B0E929B296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        // $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        // $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2512469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        // $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        // $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id)');
        // $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');

         // Create 'belongs_to' table
         $this->addSql('CREATE TABLE belongs_to (user_id INT NOT NULL, team_id INT NOT NULL, team_roles JSONB NOT NULL, validated BOOLEAN NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP DEFAULT NULL, PRIMARY KEY(user_id, team_id))');

        // Create 'category' table
        $this->addSql('CREATE TABLE category (id SERIAL PRIMARY KEY, team_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP DEFAULT NULL)');

        // Create 'task' table
        $this->addSql('CREATE TABLE task (id SERIAL PRIMARY KEY, category_id INT DEFAULT NULL, team_id INT NOT NULL, assigned_to_id INT DEFAULT NULL, created_by_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, difficulty SMALLINT NOT NULL, accept_deadline TIMESTAMP NOT NULL, completion_deadline TIMESTAMP NOT NULL, datetime_accepted TIMESTAMP DEFAULT NULL, datetime_completed TIMESTAMP DEFAULT NULL, status SMALLINT DEFAULT 0 NOT NULL, rejected BOOLEAN NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP DEFAULT NULL)');

        // Create 'team' table
        $this->addSql('CREATE TABLE team (id SERIAL PRIMARY KEY, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP DEFAULT NULL)');

        // Create 'user' table
        $this->addSql('CREATE TABLE "user" (id SERIAL PRIMARY KEY, email VARCHAR(180) NOT NULL UNIQUE, roles JSONB NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP DEFAULT NULL)');

        // Create 'messenger_messages' table
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL PRIMARY KEY, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP NOT NULL, available_at TIMESTAMP NOT NULL, delivered_at TIMESTAMP DEFAULT NULL)');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE belongs_to ADD CONSTRAINT FK_user FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE belongs_to ADD CONSTRAINT FK_team FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_team FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_category FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_team FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_assigned_to FOREIGN KEY (assigned_to_id) REFERENCES "user" (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_created_by FOREIGN KEY (created_by_id) REFERENCES "user" (id)');
    }

    public function down(Schema $schema): void
    {
        // // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE belongs_to DROP FOREIGN KEY FK_4B0E929BA76ED395');
        // $this->addSql('ALTER TABLE belongs_to DROP FOREIGN KEY FK_4B0E929B296CD8AE');
        // $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1296CD8AE');
        // $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2512469DE2');
        // $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25296CD8AE');
        // $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25F4BD7827');
        // $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25B03A8386');
        // $this->addSql('DROP TABLE belongs_to');
        // $this->addSql('DROP TABLE category');
        // $this->addSql('DROP TABLE task');
        // $this->addSql('DROP TABLE team');
        // $this->addSql('DROP TABLE user');
        // $this->addSql('DROP TABLE messenger_messages');

         // Drop foreign key constraints
         $this->addSql('ALTER TABLE belongs_to DROP CONSTRAINT FK_user');
         $this->addSql('ALTER TABLE belongs_to DROP CONSTRAINT FK_team');
         $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_team');
         $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_category');
         $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_team');
         $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_assigned_to');
         $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_created_by');
 
         // Drop tables
         $this->addSql('DROP TABLE belongs_to');
         $this->addSql('DROP TABLE category');
         $this->addSql('DROP TABLE task');
         $this->addSql('DROP TABLE team');
         $this->addSql('DROP TABLE "user"');
         $this->addSql('DROP TABLE messenger_messages');
    }
}
