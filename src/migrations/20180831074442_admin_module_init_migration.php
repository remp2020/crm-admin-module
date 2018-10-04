<?php

use Phinx\Migration\AbstractMigration;

class AdminModuleInitMigration extends AbstractMigration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';


CREATE TABLE IF NOT EXISTS `snippets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sorting` int(11) NOT NULL DEFAULT '100',
  `html` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `last_used` datetime DEFAULT NULL,
  `total_used` int(11) NOT NULL DEFAULT '0',
  `has_default_value` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2018-08-31 07:42:42
SQL;
        $this->execute($sql);
    }

    public function down()
    {
        // TODO: [refactoring] add down migrations for module init migrations
        $this->output->writeln('Down migration is not available.');
    }
}
