<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240331203959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, categorie_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, contenu LONGTEXT NOT NULL, INDEX IDX_23A0E66BCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, article_id INT NOT NULL, commentaire_parent_id INT DEFAULT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_67F068BCFB88E14F (utilisateur_id), INDEX IDX_67F068BC7294869C (article_id), INDEX IDX_67F068BCFDED4547 (commentaire_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emoji (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favori (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, article_id INT NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_EF85A2CCFB88E14F (utilisateur_id), INDEX IDX_EF85A2CC7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reaction (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, commentaire_id INT NOT NULL, emoji_id INT NOT NULL, INDEX IDX_A4D707F7FB88E14F (utilisateur_id), INDEX IDX_A4D707F7BA9CD190 (commentaire_id), INDEX IDX_A4D707F7E2462A5B (emoji_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(180) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCFDED4547 FOREIGN KEY (commentaire_parent_id) REFERENCES commentaire (id)');
        $this->addSql('ALTER TABLE favori ADD CONSTRAINT FK_EF85A2CCFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE favori ADD CONSTRAINT FK_EF85A2CC7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F7FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F7BA9CD190 FOREIGN KEY (commentaire_id) REFERENCES commentaire (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F7E2462A5B FOREIGN KEY (emoji_id) REFERENCES emoji (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66BCF5E72D');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCFB88E14F');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC7294869C');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCFDED4547');
        $this->addSql('ALTER TABLE favori DROP FOREIGN KEY FK_EF85A2CCFB88E14F');
        $this->addSql('ALTER TABLE favori DROP FOREIGN KEY FK_EF85A2CC7294869C');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F7FB88E14F');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F7BA9CD190');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F7E2462A5B');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE emoji');
        $this->addSql('DROP TABLE favori');
        $this->addSql('DROP TABLE reaction');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
