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

        $this->execute("ALTER TABLE country MODIFY COLUMN iso2 CHAR(8) NOT NULL;");
        $this->execute("ALTER TABLE country MODIFY COLUMN iso3 CHAR(8) NOT NULL;");
        $this->execute("INSERT INTO `country` VALUES('', 'NULL', 'Null', 'Null', 'NULL', '000', 'no', '00', '');");
        $this->execute("ALTER TABLE list ADD COLUMN server INT(11) DEFAULT NULL AFTER status;");
        $this->execute("ALTER TABLE user MODIFY COLUMN roleID BIGINT(20) UNSIGNED DEFAULT NULL;");
        $this->execute("ALTER TABLE state MODIFY COLUMN code CHAR(8) NOT NULL;");
        $this->execute("INSERT INTO `state` VALUES('', 'NULL', 'Null');");
        $this->execute("ALTER TABLE subscriber MODIFY COLUMN bounces INT(11) DEFAULT '0';");
        $this->execute("ALTER TABLE user_roles MODIFY COLUMN roleID BIGINT(20) UNSIGNED;");

        $this->execute(
            "CREATE TABLE `server` (
            `id` int(11) NOT NULL,
            `name` varchar(191) NOT NULL,
            `hname` varchar(191) NOT NULL,
            `uname` varchar(191) NOT NULL,
            `password` text NOT NULL,
            `port` int(11) NOT NULL,
            `protocol` enum('none','tls','notls','starttls','ssl') NOT NULL,
            `throttle` decimal(10,4) NOT NULL DEFAULT '0.0000',
            `femail` varchar(191) NOT NULL,
            `fname` varchar(191) NOT NULL,
            `remail` varchar(191) NOT NULL,
            `rname` varchar(191) NOT NULL,
            `owner` int(11) NOT NULL,
            `addDate` datetime NOT NULL,
            `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;"
        );
        $this->execute("
            ALTER TABLE `server`
            ADD PRIMARY KEY (`id`),
            ADD KEY `server_owner` (`owner`);
            
            ALTER TABLE `server`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"
        );
        
        $this->execute(
            "CREATE TABLE `template` (
            `id` int(11) NOT NULL,
            `name` varchar(191) NOT NULL,
            `description` varchar(191) NOT NULL,
            `content` longtext NOT NULL,
            `owner` int(11) NOT NULL,
            `addDate` datetime NOT NULL,
            `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;"
        );
        $this->execute("
            ALTER TABLE `template`
            ADD PRIMARY KEY (`id`),
            ADD KEY `template_owner` (`owner`);
            
            ALTER TABLE `template`
              MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"
        );
        
        $this->execute(
            "CREATE TABLE `tracking_link` (
            `id` bigint(20) NOT NULL,
            `cid` bigint(20) NOT NULL,
            `sid` bigint(20) NOT NULL,
            `source` varchar(191) NOT NULL,
            `medium` varchar(191) NOT NULL,
            `url` text NOT NULL,
            `clicked` int(11) NOT NULL,
            `addDate` datetime NOT NULL,
            `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;"
        );
        $this->execute("
            ALTER TABLE `tracking_link`
            ADD PRIMARY KEY (`id`),
            ADD KEY `tracking_link_sid` (`sid`);
            
            ALTER TABLE `tracking_link`
            MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;"
        );

        $this->execute("ALTER TABLE campaign DROP INDEX subject;");
        $this->execute("ALTER TABLE campaign DROP FOREIGN KEY `campaign_ibfk_1`;");
        $this->execute("ALTER TABLE campaign ADD CONSTRAINT `campaign_owner` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->execute("ALTER TABLE campaign_list DROP FOREIGN KEY `campaign_list_ibfk_1`;");
        $this->execute("ALTER TABLE campaign_list DROP FOREIGN KEY `campaign_list_ibfk_2`;");
        $this->execute("ALTER TABLE campaign_list ADD CONSTRAINT `campaign_list_cid` FOREIGN KEY (`cid`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE campaign_list ADD CONSTRAINT `campaign_list_lid` FOREIGN KEY (`lid`) REFERENCES `list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->execute("ALTER TABLE list ADD CONSTRAINT `list_owner` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE list ADD CONSTRAINT `list_server` FOREIGN KEY (`server`) REFERENCES `server` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
        
        $this->execute("ALTER TABLE server ADD CONSTRAINT `server_owner` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->execute("ALTER TABLE subscriber MODIFY COLUMN state CHAR(8) DEFAULT NULL;");
        $this->execute("ALTER TABLE subscriber MODIFY COLUMN country CHAR(8) DEFAULT NULL;");
        $this->execute("ALTER TABLE subscriber DROP FOREIGN KEY `subscriber_ibfk_1`;");
        $this->execute("ALTER TABLE subscriber ADD CONSTRAINT `subscriber_addedBy` FOREIGN KEY (`addedBy`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->execute("ALTER TABLE subscriber_list DROP FOREIGN KEY `subscriber_list_ibfk_1`;");
        $this->execute("ALTER TABLE subscriber_list DROP FOREIGN KEY `subscriber_list_ibfk_2`;");
        $this->execute("ALTER TABLE subscriber_list ADD CONSTRAINT `subscriber_lid` FOREIGN KEY (`lid`) REFERENCES `list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE subscriber_list ADD CONSTRAINT `subscriber_sid` FOREIGN KEY (`sid`) REFERENCES `subscriber` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE subscriber ADD CONSTRAINT `subscriber_state` FOREIGN KEY (`state`) REFERENCES `state` (`code`) ON DELETE SET NULL ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE subscriber ADD CONSTRAINT `subscriber_country` FOREIGN KEY (`country`) REFERENCES `country` (`iso2`) ON DELETE SET NULL ON UPDATE CASCADE;");
        
        $this->execute("ALTER TABLE template ADD CONSTRAINT `template_owner` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->execute("ALTER TABLE tracking DROP FOREIGN KEY `tracking_ibfk_1`;");
        $this->execute("ALTER TABLE tracking DROP FOREIGN KEY `tracking_ibfk_2`;");
        $this->execute("ALTER TABLE tracking ADD CONSTRAINT `tracking_cid` FOREIGN KEY (`cid`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE tracking ADD CONSTRAINT `tracking_sid` FOREIGN KEY (`sid`) REFERENCES `subscriber` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        
        $this->execute("ALTER TABLE tracking_link ADD CONSTRAINT `tracking_link_cid` FOREIGN KEY (`cid`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE tracking_link ADD CONSTRAINT `tracking_link_sid` FOREIGN KEY (`sid`) REFERENCES `subscriber` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->execute("ALTER TABLE user MODIFY COLUMN state CHAR(8) DEFAULT NULL;");
        $this->execute("ALTER TABLE user MODIFY COLUMN country CHAR(8) DEFAULT NULL;");
        $this->execute("ALTER TABLE user ADD CONSTRAINT `user_roleID` FOREIGN KEY (`roleID`) REFERENCES `role` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE user ADD CONSTRAINT `user_state` FOREIGN KEY (`state`) REFERENCES `state` (`code`) ON DELETE SET NULL ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE user ADD CONSTRAINT `user_country` FOREIGN KEY (`country`) REFERENCES `country` (`iso2`) ON DELETE SET NULL ON UPDATE CASCADE;");

        $this->execute("ALTER TABLE user_perms DROP FOREIGN KEY `user_perms_ibfk_1`;");
        $this->execute("ALTER TABLE user_perms ADD CONSTRAINT `user_perms_userID` FOREIGN KEY (`userID`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->execute("ALTER TABLE user_roles DROP FOREIGN KEY `user_roles_ibfk_1`;");
        $this->execute("ALTER TABLE user_roles ADD CONSTRAINT `user_roles_userID` FOREIGN KEY (`userID`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        $this->execute("ALTER TABLE user_roles ADD CONSTRAINT `user_roles_roleID` FOREIGN KEY (`roleID`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        // re-arm foreign key checks
        $this->execute('SET FOREIGN_KEY_CHECKS=1;');
    }
}
