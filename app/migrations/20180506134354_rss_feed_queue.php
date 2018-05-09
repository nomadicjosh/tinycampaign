<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class RssFeedQueue extends AbstractMigration
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

        $this->execute("ALTER TABLE `campaign` ADD COLUMN `last_queued` datetime DEFAULT NULL AFTER `sendfinish`;");

        $this->execute("ALTER TABLE `campaign` MODIFY COLUMN `node` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL;");

        $this->execute("ALTER TABLE `list` ADD COLUMN `unsub_mailto` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `name`;");

        // Migration for table campaign_queue
        if (!$this->hasTable('campaign_queue')) :
            $table = $this->table('campaign_queue', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                    ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                    ->addColumn('tmp_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR])
                    ->addColumn('lid', 'integer', ['limit' => MysqlAdapter::INT_BIG])
                    ->addColumn('cid', 'integer', ['limit' => MysqlAdapter::INT_BIG])
                    ->addColumn('sid', 'integer', ['limit' => MysqlAdapter::INT_BIG])
                    ->addColumn('to_email', 'string', ['limit' => 191])
                    ->addColumn('to_name', 'string', ['limit' => 191])
                    ->addColumn('timestamp_created', 'datetime', ['null' => true])
                    ->addColumn('timestamp_to_send', 'datetime', ['null' => true])
                    ->addColumn('timestamp_sent', 'datetime', ['null' => true])
                    ->addColumn('is_unsubscribed', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_REGULAR])
                    ->addColumn('timestamp_unsubscribed', 'datetime', ['null' => true])
                    ->addColumn('is_priority', 'enum', ['default' => 'false', 'values' => ['true', 'false']])
                    ->addColumn('is_immediate', 'enum', ['default' => 'false', 'values' => ['true', 'false']])
                    ->addColumn('is_sent', 'enum', ['default' => 'false', 'values' => ['true', 'false']])
                    ->addColumn('is_cancelled', 'enum', ['default' => 'false', 'values' => ['true', 'false']])
                    ->addColumn('is_blocked', 'enum', ['default' => 'false', 'values' => ['true', 'false']])
                    ->addColumn('send_count', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_REGULAR])
                    ->addColumn('error_count', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_REGULAR])
                    ->addColumn('LastUpdate', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                    ->addForeignKey('lid', 'list', 'id', ['constraint' => 'campaign_queue_lid', 'delete' => 'CASCADE', 'update' => 'CASCADE'])
                    ->addForeignKey('cid', 'campaign', 'id', ['constraint' => 'campaign_queue_cid', 'delete' => 'CASCADE', 'update' => 'CASCADE'])
                    ->addForeignKey('sid', 'subscriber', 'id', ['constraint' => 'campaign_queue_sid', 'delete' => 'CASCADE', 'update' => 'CASCADE'])
                    ->create();
        endif;

        // Migration for table rss_campaign
        if (!$this->hasTable('rss_campaign')) :
            $table = $this->table('rss_campaign', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                    ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                    ->addColumn('owner', 'integer', ['limit' => MysqlAdapter::INT_REGULAR])
                    ->addColumn('node', 'string', ['limit' => 191])
                    ->addColumn('subject', 'string', ['default' => '', 'limit' => 191])
                    ->addColumn('from_name', 'string', ['limit' => 191])
                    ->addColumn('from_email', 'string', ['limit' => 191])
                    ->addColumn('rss_feed', 'string', ['limit' => 191])
                    ->addColumn('lid', 'string', ['limit' => 191])
                    ->addColumn('tid', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR])
                    ->addColumn('status', 'enum', ['default' => 'active', 'values' => ['active', 'inactive']])
                    ->addColumn('addDate', 'datetime', [])
                    ->addColumn('LastUpdate', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                    ->addForeignKey('tid', 'template', 'id', ['constraint' => 'rss_campaign_tid', 'delete' => 'SET_NULL', 'update' => 'CASCADE'])
                    ->addForeignKey('owner', 'user', 'id', ['constraint' => 'rss_campaign_owner', 'delete' => 'CASCADE', 'update' => 'CASCADE'])
                    ->create();
        endif;

        // Migration for table rss_list
        if (!$this->hasTable('rss_guid')) :
            $table = $this->table('rss_guid', ['id' => false, 'primary_key' => 'guid', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                    ->addColumn('guid', 'string', ['limit' => 191])
                    ->create();
        endif;

        // re-arm foreign key checks
        $this->execute('SET FOREIGN_KEY_CHECKS=1;');
    }

}
