<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ServerTable extends AbstractMigration
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
        
        $table = $this->table('country');
        $table
            ->addIndex(['iso2'])
            ->addIndex(['iso3'])
            ->save();
        
        $this->execute("ALTER TABLE list ADD COLUMN server INT(11) DEFAULT NULL AFTER status;");

        if(!$this->hasTable('server')) :
        $this->execute(
            "CREATE TABLE `server` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `hname` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `uname` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `password` text COLLATE utf8mb4_unicode_ci NOT NULL,
              `port` int(11) NOT NULL,
              `protocol` enum('none','tls','notls','starttls','ssl') COLLATE utf8mb4_unicode_ci NOT NULL,
              `throttle` decimal(10,4) NOT NULL DEFAULT '0.0000',
              `femail` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `fname` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `remail` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `rname` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `owner` int(11) NOT NULL,
              `addDate` datetime NOT NULL,
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `server_owner` (`owner`),
              CONSTRAINT `server_owner` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
        endif;
        
        if(!$this->hasTable('template')) :
        $this->execute(
            "CREATE TABLE `template` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `owner` int(11) NOT NULL,
              `addDate` datetime NOT NULL,
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `template_owner` (`owner`),
              CONSTRAINT `template_owner` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
        endif;
        
        if(!$this->hasTable('tracking_link')) :
        $this->execute(
            "CREATE TABLE `tracking_link` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `cid` bigint(20) NOT NULL,
              `sid` bigint(20) NOT NULL,
              `source` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `medium` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
              `url` text COLLATE utf8mb4_unicode_ci NOT NULL,
              `clicked` int(11) NOT NULL,
              `addDate` datetime NOT NULL,
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `tracking_link_sid` (`sid`),
              KEY `tracking_link_cid` (`cid`),
              CONSTRAINT `tracking_link_cid` FOREIGN KEY (`cid`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `tracking_link_sid` FOREIGN KEY (`sid`) REFERENCES `subscriber` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
        endif;

        // re-arm foreign key checks
        $this->execute('SET FOREIGN_KEY_CHECKS=1;');
    }
}
