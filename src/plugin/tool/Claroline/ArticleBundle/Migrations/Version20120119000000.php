<?php

namespace Claroline\ArticleBundle\Migrations;

use Claroline\CoreBundle\Installation\BundleMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120119000000 extends BundleMigration
{
    public function up(Schema $schema)
    {
        $this->createArticleTable($schema);
    }
    
    public function down(Schema $schema)
    {
        $schema->dropTable('claro_article');
    }
    
    private function createArticleTable(Schema $schema)
    {
        $table = $schema->createTable('claro_article');
        
        $this->addId($table);        
        $table->addColumn('title', 'string', array('length' => 255));
        $table->addForeignKeyConstraint(
            $schema->getTable('claro_text'),
            array('id'), 
            array('id'),
            array("onDelete" => "CASCADE")
        );
    }
}