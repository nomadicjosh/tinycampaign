<?php

use Phinx\Migration\AbstractMigration;

class SubscriberIndices extends AbstractMigration
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
        $this->execute('ALTER TABLE `subscriber` ADD INDEX `subscriber_fname` (`fname`);');
        
        $this->execute('ALTER TABLE `subscriber` ADD INDEX `subscriber_lname` (`lname`);');
        
        $this->execute('ALTER TABLE `subscriber` ADD INDEX `subscriber_email` (`email`);');
        
        $this->execute('ALTER TABLE `subscriber` ADD INDEX `subscriber_allowed` (`allowed`);');
        
        $this->execute('ALTER TABLE `subscriber` ADD INDEX `subscriber_bounces` (`bounces`);');
        
        $this->execute('ALTER TABLE `subscriber` ADD INDEX `subscriber_spammer` (`spammer`);');
        
        $this->execute('ALTER TABLE `subscriber` ADD INDEX `subscriber_exception` (`exception`);');
        
        $this->execute('ALTER TABLE `subscriber_list` ADD INDEX `subscriber_list_confirmed` (`confirmed`);');
        
        $this->execute('ALTER TABLE `subscriber_list` ADD INDEX `subscriber_list_unsubscribed` (`unsubscribed`);');
    }
}
