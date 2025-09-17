<?php

declare(strict_types=1);

namespace MauticPlugin\MauticOpportunitiesBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version_1_6_0 extends AbstractMauticMigration
{
    protected function mysqlUp(Schema $schema): void
    {
        $tableName = $this->getPrefixedTableName('opportunities');

        if ($schema->hasTable($tableName)) {
            $table = $schema->getTable($tableName);

            // Add form_type_c field
            if (!$table->hasColumn('form_type_c')) {
                $this->addSql("ALTER TABLE $tableName ADD COLUMN form_type_c VARCHAR(255) DEFAULT NULL");
            }
        }
    }

    protected function mysqlDown(Schema $schema): void
    {
        $tableName = $this->getPrefixedTableName('opportunities');

        if ($schema->hasTable($tableName)) {
            $table = $schema->getTable($tableName);

            // Remove form_type_c field
            if ($table->hasColumn('form_type_c')) {
                $this->addSql("ALTER TABLE $tableName DROP COLUMN form_type_c");
            }
        }
    }
}