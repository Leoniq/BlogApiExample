<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615134313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE post_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql("
            CREATE TABLE IF NOT EXISTS post
                (
                    id INT NOT NULL DEFAULT nextval('post_id_seq'::regclass),
                    title VARCHAR(255),
                    description VARCHAR(255),
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT now(),
                    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT now(),
                    PRIMARY KEY(id)
                )
            "
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE post_id_seq CASCADE');
        $this->addSql('DROP TABLE post');
    }
}
