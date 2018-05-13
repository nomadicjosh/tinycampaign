<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class RssGuid extends AbstractMigration
{

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {

        // disable foreign key checks to ensure updates without issues.
        $this->execute('SET FOREIGN_KEY_CHECKS=0;');

        // Migration for table rss_list
        if ($this->hasTable('rss_guid')) :
            /**
             * Drop current rss_guid table.
             */
            $this->execute('DROP TABLE `rss_guid`;');

            /**
             * Recreate rss_guid table.
             */
            $table = $this->table('rss_guid', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                    ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                    ->addColumn('rcid', 'integer', ['limit' => MysqlAdapter::INT_BIG])
                    ->addColumn('guid', 'string', ['limit' => 191])
                    ->addColumn('addDate', 'datetime', [])
                    ->addColumn('LastUpdate', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                    ->addForeignKey('rcid', 'rss_campaign', 'id', ['constraint' => 'rss_guid_rcid', 'delete' => 'CASCADE', 'update' => 'CASCADE'])
                    ->create();
        endif;

        // re-arm foreign key checks
        $this->execute('SET FOREIGN_KEY_CHECKS=1;');
    }

}
