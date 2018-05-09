<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class InitialSchema extends AbstractMigration
{

    public function change()
    {
        $NOW = date("Y-m-d H:i:s");
        $factory = new RandomLib\Factory;
        $generator = $factory->getGenerator(new SecurityLib\Strength(SecurityLib\Strength::MEDIUM));
        $api_key = $generator->generateString(20, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

        $host = $this->adapter->getOption('host');
        $name = $this->adapter->getOption('name');
        $user = $this->adapter->getOption('user');
        $pass = $this->adapter->getOption('pass');

        // disable foreign key checks to ensure all tables can be initially created
        $this->execute('SET FOREIGN_KEY_CHECKS=0;');

        // Migration for table activity
        if (!$this->hasTable('activity')) :
            $table = $this->table('activity', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('action', 'string', array('limit' => 191))
                ->addColumn('process', 'string', array('limit' => 191))
                ->addColumn('record', 'text', array('null' => true, 'limit' => MysqlAdapter::TEXT_LONG))
                ->addColumn('uname', 'string', array('limit' => 191))
                ->addColumn('created_at', 'datetime', [])
                ->addColumn('expires_at', 'datetime', [])
                ->create();
        endif;

        // Migration for table campaign
        if (!$this->hasTable('campaign')) :
            $table = $this->table('campaign', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('owner', 'integer', array('limit' => MysqlAdapter::INT_REGULAR))
                ->addColumn('node', 'string', array('limit' => 191))
                ->addColumn('subject', 'string', array('default' => '', 'limit' => 191))
                ->addColumn('from_name', 'string', array('limit' => 191))
                ->addColumn('from_email', 'string', array('limit' => 191))
                ->addColumn('html', 'text', ['limit' => MysqlAdapter::TEXT_LONG])
                ->addColumn('text', 'text', array('null' => true, 'limit' => MysqlAdapter::TEXT_LONG))
                ->addColumn('footer', 'text', array('null' => true, 'limit' => MysqlAdapter::TEXT_REGULAR))
                ->addColumn('attachment', 'text', array('null' => true))
                ->addColumn('status', 'enum', array('default' => 'ready', 'values' => array('ready', 'processing', 'paused', 'sent')))
                ->addColumn('sendstart', 'datetime', ['null' => true])
                ->addColumn('sendfinish', 'datetime', array('null' => true))
                ->addColumn('recipients', 'integer', array('default' => '0', 'limit' => 11))
                ->addColumn('viewed', 'integer', array('default' => '0', 'limit' => 11))
                ->addColumn('bounces', 'integer', array('default' => '0', 'limit' => 11))
                ->addColumn('archive', 'enum', array('default' => '0', 'values' => array('1', '0')))
                ->addColumn('addDate', 'datetime', [])
                ->addColumn('LastUpdate', 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'))
                ->addForeignKey('owner', 'user', 'id', array('constraint' => 'campaign_owner', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->create();
        endif;

        // Migration for table campaign_list
        if (!$this->hasTable('campaign_list')) :
            $table = $this->table('campaign_list', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('cid', 'integer', array('limit' => MysqlAdapter::INT_BIG))
                ->addColumn('lid', 'integer', array('limit' => MysqlAdapter::INT_BIG))
                ->addForeignKey('cid', 'campaign', 'id', array('constraint' => 'campaign_list_cid', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->addForeignKey('lid', 'list', 'id', array('constraint' => 'campaign_list_lid', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->create();
        endif;
        
        // Migration for table campaign
        if (!$this->hasTable('campaign_queue')) :
            $table = $this->table('campaign_queue', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                    ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
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

        // Migration for table country
        if (!$this->hasTable('country')) :
            $table = $this->table('country', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => 5])
                ->addColumn('iso2', 'char', ['null' => true, 'limit' => 8])
                ->addColumn('short_name', 'string', ['default' => '', 'limit' => 191])
                ->addColumn('long_name', 'string', ['default' => '', 'limit' => 191])
                ->addColumn('iso3', 'char', ['null' => true, 'limit' => 8])
                ->addColumn('numcode', 'string', ['null' => true, 'limit' => 12])
                ->addColumn('un_member', 'string', ['null' => true, 'limit' => 24])
                ->addColumn('calling_code', 'string', ['null' => true, 'limit' => 12])
                ->addColumn('cctld', 'char', ['null' => true, 'limit' => 5])
                ->create();

            $this->execute("INSERT INTO `country` VALUES(1, 'AF', 'Afghanistan', 'Islamic Republic of Afghanistan', 'AFG', '004', 'yes', '93', '.af');");
            $this->execute("INSERT INTO `country` VALUES(2, 'AX', 'Aland Islands', '&Aring;land Islands', 'ALA', '248', 'no', '358', '.ax');");
            $this->execute("INSERT INTO `country` VALUES(3, 'AL', 'Albania', 'Republic of Albania', 'ALB', '008', 'yes', '355', '.al');");
            $this->execute("INSERT INTO `country` VALUES(4, 'DZ', 'Algeria', 'People''s Democratic Republic of Algeria', 'DZA', '012', 'yes', '213', '.dz');");
            $this->execute("INSERT INTO `country` VALUES(5, 'AS', 'American Samoa', 'American Samoa', 'ASM', '016', 'no', '1+684', '.as');");
            $this->execute("INSERT INTO `country` VALUES(6, 'AD', 'Andorra', 'Principality of Andorra', 'AND', '020', 'yes', '376', '.ad');");
            $this->execute("INSERT INTO `country` VALUES(7, 'AO', 'Angola', 'Republic of Angola', 'AGO', '024', 'yes', '244', '.ao');");
            $this->execute("INSERT INTO `country` VALUES(8, 'AI', 'Anguilla', 'Anguilla', 'AIA', '660', 'no', '1+264', '.ai');");
            $this->execute("INSERT INTO `country` VALUES(9, 'AQ', 'Antarctica', 'Antarctica', 'ATA', '010', 'no', '672', '.aq');");
            $this->execute("INSERT INTO `country` VALUES(10, 'AG', 'Antigua and Barbuda', 'Antigua and Barbuda', 'ATG', '028', 'yes', '1+268', '.ag');");
            $this->execute("INSERT INTO `country` VALUES(11, 'AR', 'Argentina', 'Argentine Republic', 'ARG', '032', 'yes', '54', '.ar');");
            $this->execute("INSERT INTO `country` VALUES(12, 'AM', 'Armenia', 'Republic of Armenia', 'ARM', '051', 'yes', '374', '.am');");
            $this->execute("INSERT INTO `country` VALUES(13, 'AW', 'Aruba', 'Aruba', 'ABW', '533', 'no', '297', '.aw');");
            $this->execute("INSERT INTO `country` VALUES(14, 'AU', 'Australia', 'Commonwealth of Australia', 'AUS', '036', 'yes', '61', '.au');");
            $this->execute("INSERT INTO `country` VALUES(15, 'AT', 'Austria', 'Republic of Austria', 'AUT', '040', 'yes', '43', '.at');");
            $this->execute("INSERT INTO `country` VALUES(16, 'AZ', 'Azerbaijan', 'Republic of Azerbaijan', 'AZE', '031', 'yes', '994', '.az');");
            $this->execute("INSERT INTO `country` VALUES(17, 'BS', 'Bahamas', 'Commonwealth of The Bahamas', 'BHS', '044', 'yes', '1+242', '.bs');");
            $this->execute("INSERT INTO `country` VALUES(18, 'BH', 'Bahrain', 'Kingdom of Bahrain', 'BHR', '048', 'yes', '973', '.bh');");
            $this->execute("INSERT INTO `country` VALUES(19, 'BD', 'Bangladesh', 'People''s Republic of Bangladesh', 'BGD', '050', 'yes', '880', '.bd');");
            $this->execute("INSERT INTO `country` VALUES(20, 'BB', 'Barbados', 'Barbados', 'BRB', '052', 'yes', '1+246', '.bb');");
            $this->execute("INSERT INTO `country` VALUES(21, 'BY', 'Belarus', 'Republic of Belarus', 'BLR', '112', 'yes', '375', '.by');");
            $this->execute("INSERT INTO `country` VALUES(22, 'BE', 'Belgium', 'Kingdom of Belgium', 'BEL', '056', 'yes', '32', '.be');");
            $this->execute("INSERT INTO `country` VALUES(23, 'BZ', 'Belize', 'Belize', 'BLZ', '084', 'yes', '501', '.bz');");
            $this->execute("INSERT INTO `country` VALUES(24, 'BJ', 'Benin', 'Republic of Benin', 'BEN', '204', 'yes', '229', '.bj');");
            $this->execute("INSERT INTO `country` VALUES(25, 'BM', 'Bermuda', 'Bermuda Islands', 'BMU', '060', 'no', '1+441', '.bm');");
            $this->execute("INSERT INTO `country` VALUES(26, 'BT', 'Bhutan', 'Kingdom of Bhutan', 'BTN', '064', 'yes', '975', '.bt');");
            $this->execute("INSERT INTO `country` VALUES(27, 'BO', 'Bolivia', 'Plurinational State of Bolivia', 'BOL', '068', 'yes', '591', '.bo');");
            $this->execute("INSERT INTO `country` VALUES(28, 'BQ', 'Bonaire, Sint Eustatius and Saba', 'Bonaire, Sint Eustatius and Saba', 'BES', '535', 'no', '599', '.bq');");
            $this->execute("INSERT INTO `country` VALUES(29, 'BA', 'Bosnia and Herzegovina', 'Bosnia and Herzegovina', 'BIH', '070', 'yes', '387', '.ba');");
            $this->execute("INSERT INTO `country` VALUES(30, 'BW', 'Botswana', 'Republic of Botswana', 'BWA', '072', 'yes', '267', '.bw');");
            $this->execute("INSERT INTO `country` VALUES(31, 'BV', 'Bouvet Island', 'Bouvet Island', 'BVT', '074', 'no', 'NONE', '.bv');");
            $this->execute("INSERT INTO `country` VALUES(32, 'BR', 'Brazil', 'Federative Republic of Brazil', 'BRA', '076', 'yes', '55', '.br');");
            $this->execute("INSERT INTO `country` VALUES(33, 'IO', 'British Indian Ocean Territory', 'British Indian Ocean Territory', 'IOT', '086', 'no', '246', '.io');");
            $this->execute("INSERT INTO `country` VALUES(34, 'BN', 'Brunei', 'Brunei Darussalam', 'BRN', '096', 'yes', '673', '.bn');");
            $this->execute("INSERT INTO `country` VALUES(35, 'BG', 'Bulgaria', 'Republic of Bulgaria', 'BGR', '100', 'yes', '359', '.bg');");
            $this->execute("INSERT INTO `country` VALUES(36, 'BF', 'Burkina Faso', 'Burkina Faso', 'BFA', '854', 'yes', '226', '.bf');");
            $this->execute("INSERT INTO `country` VALUES(37, 'BI', 'Burundi', 'Republic of Burundi', 'BDI', '108', 'yes', '257', '.bi');");
            $this->execute("INSERT INTO `country` VALUES(38, 'KH', 'Cambodia', 'Kingdom of Cambodia', 'KHM', '116', 'yes', '855', '.kh');");
            $this->execute("INSERT INTO `country` VALUES(39, 'CM', 'Cameroon', 'Republic of Cameroon', 'CMR', '120', 'yes', '237', '.cm');");
            $this->execute("INSERT INTO `country` VALUES(40, 'CA', 'Canada', 'Canada', 'CAN', '124', 'yes', '1', '.ca');");
            $this->execute("INSERT INTO `country` VALUES(41, 'CV', 'Cape Verde', 'Republic of Cape Verde', 'CPV', '132', 'yes', '238', '.cv');");
            $this->execute("INSERT INTO `country` VALUES(42, 'KY', 'Cayman Islands', 'The Cayman Islands', 'CYM', '136', 'no', '1+345', '.ky');");
            $this->execute("INSERT INTO `country` VALUES(43, 'CF', 'Central African Republic', 'Central African Republic', 'CAF', '140', 'yes', '236', '.cf');");
            $this->execute("INSERT INTO `country` VALUES(44, 'TD', 'Chad', 'Republic of Chad', 'TCD', '148', 'yes', '235', '.td');");
            $this->execute("INSERT INTO `country` VALUES(45, 'CL', 'Chile', 'Republic of Chile', 'CHL', '152', 'yes', '56', '.cl');");
            $this->execute("INSERT INTO `country` VALUES(46, 'CN', 'China', 'People''s Republic of China', 'CHN', '156', 'yes', '86', '.cn');");
            $this->execute("INSERT INTO `country` VALUES(47, 'CX', 'Christmas Island', 'Christmas Island', 'CXR', '162', 'no', '61', '.cx');");
            $this->execute("INSERT INTO `country` VALUES(48, 'CC', 'Cocos (Keeling) Islands', 'Cocos (Keeling) Islands', 'CCK', '166', 'no', '61', '.cc');");
            $this->execute("INSERT INTO `country` VALUES(49, 'CO', 'Colombia', 'Republic of Colombia', 'COL', '170', 'yes', '57', '.co');");
            $this->execute("INSERT INTO `country` VALUES(50, 'KM', 'Comoros', 'Union of the Comoros', 'COM', '174', 'yes', '269', '.km');");
            $this->execute("INSERT INTO `country` VALUES(51, 'CG', 'Congo', 'Republic of the Congo', 'COG', '178', 'yes', '242', '.cg');");
            $this->execute("INSERT INTO `country` VALUES(52, 'CK', 'Cook Islands', 'Cook Islands', 'COK', '184', 'some', '682', '.ck');");
            $this->execute("INSERT INTO `country` VALUES(53, 'CR', 'Costa Rica', 'Republic of Costa Rica', 'CRI', '188', 'yes', '506', '.cr');");
            $this->execute("INSERT INTO `country` VALUES(54, 'CI', 'Cote d''ivoire (Ivory Coast)', 'Republic of C&ocirc;te D''Ivoire (Ivory Coast)', 'CIV', '384', 'yes', '225', '.ci');");
            $this->execute("INSERT INTO `country` VALUES(55, 'HR', 'Croatia', 'Republic of Croatia', 'HRV', '191', 'yes', '385', '.hr');");
            $this->execute("INSERT INTO `country` VALUES(56, 'CU', 'Cuba', 'Republic of Cuba', 'CUB', '192', 'yes', '53', '.cu');");
            $this->execute("INSERT INTO `country` VALUES(57, 'CW', 'Curacao', 'Cura&ccedil;ao', 'CUW', '531', 'no', '599', '.cw');");
            $this->execute("INSERT INTO `country` VALUES(58, 'CY', 'Cyprus', 'Republic of Cyprus', 'CYP', '196', 'yes', '357', '.cy');");
            $this->execute("INSERT INTO `country` VALUES(59, 'CZ', 'Czech Republic', 'Czech Republic', 'CZE', '203', 'yes', '420', '.cz');");
            $this->execute("INSERT INTO `country` VALUES(60, 'CD', 'Democratic Republic of the Congo', 'Democratic Republic of the Congo', 'COD', '180', 'yes', '243', '.cd');");
            $this->execute("INSERT INTO `country` VALUES(61, 'DK', 'Denmark', 'Kingdom of Denmark', 'DNK', '208', 'yes', '45', '.dk');");
            $this->execute("INSERT INTO `country` VALUES(62, 'DJ', 'Djibouti', 'Republic of Djibouti', 'DJI', '262', 'yes', '253', '.dj');");
            $this->execute("INSERT INTO `country` VALUES(63, 'DM', 'Dominica', 'Commonwealth of Dominica', 'DMA', '212', 'yes', '1+767', '.dm');");
            $this->execute("INSERT INTO `country` VALUES(64, 'DO', 'Dominican Republic', 'Dominican Republic', 'DOM', '214', 'yes', '1+809, 8', '.do');");
            $this->execute("INSERT INTO `country` VALUES(65, 'EC', 'Ecuador', 'Republic of Ecuador', 'ECU', '218', 'yes', '593', '.ec');");
            $this->execute("INSERT INTO `country` VALUES(66, 'EG', 'Egypt', 'Arab Republic of Egypt', 'EGY', '818', 'yes', '20', '.eg');");
            $this->execute("INSERT INTO `country` VALUES(67, 'SV', 'El Salvador', 'Republic of El Salvador', 'SLV', '222', 'yes', '503', '.sv');");
            $this->execute("INSERT INTO `country` VALUES(68, 'GQ', 'Equatorial Guinea', 'Republic of Equatorial Guinea', 'GNQ', '226', 'yes', '240', '.gq');");
            $this->execute("INSERT INTO `country` VALUES(69, 'ER', 'Eritrea', 'State of Eritrea', 'ERI', '232', 'yes', '291', '.er');");
            $this->execute("INSERT INTO `country` VALUES(70, 'EE', 'Estonia', 'Republic of Estonia', 'EST', '233', 'yes', '372', '.ee');");
            $this->execute("INSERT INTO `country` VALUES(71, 'ET', 'Ethiopia', 'Federal Democratic Republic of Ethiopia', 'ETH', '231', 'yes', '251', '.et');");
            $this->execute("INSERT INTO `country` VALUES(72, 'FK', 'Falkland Islands (Malvinas)', 'The Falkland Islands (Malvinas)', 'FLK', '238', 'no', '500', '.fk');");
            $this->execute("INSERT INTO `country` VALUES(73, 'FO', 'Faroe Islands', 'The Faroe Islands', 'FRO', '234', 'no', '298', '.fo');");
            $this->execute("INSERT INTO `country` VALUES(74, 'FJ', 'Fiji', 'Republic of Fiji', 'FJI', '242', 'yes', '679', '.fj');");
            $this->execute("INSERT INTO `country` VALUES(75, 'FI', 'Finland', 'Republic of Finland', 'FIN', '246', 'yes', '358', '.fi');");
            $this->execute("INSERT INTO `country` VALUES(76, 'FR', 'France', 'French Republic', 'FRA', '250', 'yes', '33', '.fr');");
            $this->execute("INSERT INTO `country` VALUES(77, 'GF', 'French Guiana', 'French Guiana', 'GUF', '254', 'no', '594', '.gf');");
            $this->execute("INSERT INTO `country` VALUES(78, 'PF', 'French Polynesia', 'French Polynesia', 'PYF', '258', 'no', '689', '.pf');");
            $this->execute("INSERT INTO `country` VALUES(79, 'TF', 'French Southern Territories', 'French Southern Territories', 'ATF', '260', 'no', NULL, '.tf');");
            $this->execute("INSERT INTO `country` VALUES(80, 'GA', 'Gabon', 'Gabonese Republic', 'GAB', '266', 'yes', '241', '.ga');");
            $this->execute("INSERT INTO `country` VALUES(81, 'GM', 'Gambia', 'Republic of The Gambia', 'GMB', '270', 'yes', '220', '.gm');");
            $this->execute("INSERT INTO `country` VALUES(82, 'GE', 'Georgia', 'Georgia', 'GEO', '268', 'yes', '995', '.ge');");
            $this->execute("INSERT INTO `country` VALUES(83, 'DE', 'Germany', 'Federal Republic of Germany', 'DEU', '276', 'yes', '49', '.de');");
            $this->execute("INSERT INTO `country` VALUES(84, 'GH', 'Ghana', 'Republic of Ghana', 'GHA', '288', 'yes', '233', '.gh');");
            $this->execute("INSERT INTO `country` VALUES(85, 'GI', 'Gibraltar', 'Gibraltar', 'GIB', '292', 'no', '350', '.gi');");
            $this->execute("INSERT INTO `country` VALUES(86, 'GR', 'Greece', 'Hellenic Republic', 'GRC', '300', 'yes', '30', '.gr');");
            $this->execute("INSERT INTO `country` VALUES(87, 'GL', 'Greenland', 'Greenland', 'GRL', '304', 'no', '299', '.gl');");
            $this->execute("INSERT INTO `country` VALUES(88, 'GD', 'Grenada', 'Grenada', 'GRD', '308', 'yes', '1+473', '.gd');");
            $this->execute("INSERT INTO `country` VALUES(89, 'GP', 'Guadaloupe', 'Guadeloupe', 'GLP', '312', 'no', '590', '.gp');");
            $this->execute("INSERT INTO `country` VALUES(90, 'GU', 'Guam', 'Guam', 'GUM', '316', 'no', '1+671', '.gu');");
            $this->execute("INSERT INTO `country` VALUES(91, 'GT', 'Guatemala', 'Republic of Guatemala', 'GTM', '320', 'yes', '502', '.gt');");
            $this->execute("INSERT INTO `country` VALUES(92, 'GG', 'Guernsey', 'Guernsey', 'GGY', '831', 'no', '44', '.gg');");
            $this->execute("INSERT INTO `country` VALUES(93, 'GN', 'Guinea', 'Republic of Guinea', 'GIN', '324', 'yes', '224', '.gn');");
            $this->execute("INSERT INTO `country` VALUES(94, 'GW', 'Guinea-Bissau', 'Republic of Guinea-Bissau', 'GNB', '624', 'yes', '245', '.gw');");
            $this->execute("INSERT INTO `country` VALUES(95, 'GY', 'Guyana', 'Co-operative Republic of Guyana', 'GUY', '328', 'yes', '592', '.gy');");
            $this->execute("INSERT INTO `country` VALUES(96, 'HT', 'Haiti', 'Republic of Haiti', 'HTI', '332', 'yes', '509', '.ht');");
            $this->execute("INSERT INTO `country` VALUES(97, 'HM', 'Heard Island and McDonald Islands', 'Heard Island and McDonald Islands', 'HMD', '334', 'no', 'NONE', '.hm');");
            $this->execute("INSERT INTO `country` VALUES(98, 'HN', 'Honduras', 'Republic of Honduras', 'HND', '340', 'yes', '504', '.hn');");
            $this->execute("INSERT INTO `country` VALUES(99, 'HK', 'Hong Kong', 'Hong Kong', 'HKG', '344', 'no', '852', '.hk');");
            $this->execute("INSERT INTO `country` VALUES(100, 'HU', 'Hungary', 'Hungary', 'HUN', '348', 'yes', '36', '.hu');");
            $this->execute("INSERT INTO `country` VALUES(101, 'IS', 'Iceland', 'Republic of Iceland', 'ISL', '352', 'yes', '354', '.is');");
            $this->execute("INSERT INTO `country` VALUES(102, 'IN', 'India', 'Republic of India', 'IND', '356', 'yes', '91', '.in');");
            $this->execute("INSERT INTO `country` VALUES(103, 'ID', 'Indonesia', 'Republic of Indonesia', 'IDN', '360', 'yes', '62', '.id');");
            $this->execute("INSERT INTO `country` VALUES(104, 'IR', 'Iran', 'Islamic Republic of Iran', 'IRN', '364', 'yes', '98', '.ir');");
            $this->execute("INSERT INTO `country` VALUES(105, 'IQ', 'Iraq', 'Republic of Iraq', 'IRQ', '368', 'yes', '964', '.iq');");
            $this->execute("INSERT INTO `country` VALUES(106, 'IE', 'Ireland', 'Ireland', 'IRL', '372', 'yes', '353', '.ie');");
            $this->execute("INSERT INTO `country` VALUES(107, 'IM', 'Isle of Man', 'Isle of Man', 'IMN', '833', 'no', '44', '.im');");
            $this->execute("INSERT INTO `country` VALUES(108, 'IL', 'Israel', 'State of Israel', 'ISR', '376', 'yes', '972', '.il');");
            $this->execute("INSERT INTO `country` VALUES(109, 'IT', 'Italy', 'Italian Republic', 'ITA', '380', 'yes', '39', '.jm');");
            $this->execute("INSERT INTO `country` VALUES(110, 'JM', 'Jamaica', 'Jamaica', 'JAM', '388', 'yes', '1+876', '.jm');");
            $this->execute("INSERT INTO `country` VALUES(111, 'JP', 'Japan', 'Japan', 'JPN', '392', 'yes', '81', '.jp');");
            $this->execute("INSERT INTO `country` VALUES(112, 'JE', 'Jersey', 'The Bailiwick of Jersey', 'JEY', '832', 'no', '44', '.je');");
            $this->execute("INSERT INTO `country` VALUES(113, 'JO', 'Jordan', 'Hashemite Kingdom of Jordan', 'JOR', '400', 'yes', '962', '.jo');");
            $this->execute("INSERT INTO `country` VALUES(114, 'KZ', 'Kazakhstan', 'Republic of Kazakhstan', 'KAZ', '398', 'yes', '7', '.kz');");
            $this->execute("INSERT INTO `country` VALUES(115, 'KE', 'Kenya', 'Republic of Kenya', 'KEN', '404', 'yes', '254', '.ke');");
            $this->execute("INSERT INTO `country` VALUES(116, 'KI', 'Kiribati', 'Republic of Kiribati', 'KIR', '296', 'yes', '686', '.ki');");
            $this->execute("INSERT INTO `country` VALUES(117, 'XK', 'Kosovo', 'Republic of Kosovo', '---', '---', 'some', '381', '');");
            $this->execute("INSERT INTO `country` VALUES(118, 'KW', 'Kuwait', 'State of Kuwait', 'KWT', '414', 'yes', '965', '.kw');");
            $this->execute("INSERT INTO `country` VALUES(119, 'KG', 'Kyrgyzstan', 'Kyrgyz Republic', 'KGZ', '417', 'yes', '996', '.kg');");
            $this->execute("INSERT INTO `country` VALUES(120, 'LA', 'Laos', 'Lao People''s Democratic Republic', 'LAO', '418', 'yes', '856', '.la');");
            $this->execute("INSERT INTO `country` VALUES(121, 'LV', 'Latvia', 'Republic of Latvia', 'LVA', '428', 'yes', '371', '.lv');");
            $this->execute("INSERT INTO `country` VALUES(122, 'LB', 'Lebanon', 'Republic of Lebanon', 'LBN', '422', 'yes', '961', '.lb');");
            $this->execute("INSERT INTO `country` VALUES(123, 'LS', 'Lesotho', 'Kingdom of Lesotho', 'LSO', '426', 'yes', '266', '.ls');");
            $this->execute("INSERT INTO `country` VALUES(124, 'LR', 'Liberia', 'Republic of Liberia', 'LBR', '430', 'yes', '231', '.lr');");
            $this->execute("INSERT INTO `country` VALUES(125, 'LY', 'Libya', 'Libya', 'LBY', '434', 'yes', '218', '.ly');");
            $this->execute("INSERT INTO `country` VALUES(126, 'LI', 'Liechtenstein', 'Principality of Liechtenstein', 'LIE', '438', 'yes', '423', '.li');");
            $this->execute("INSERT INTO `country` VALUES(127, 'LT', 'Lithuania', 'Republic of Lithuania', 'LTU', '440', 'yes', '370', '.lt');");
            $this->execute("INSERT INTO `country` VALUES(128, 'LU', 'Luxembourg', 'Grand Duchy of Luxembourg', 'LUX', '442', 'yes', '352', '.lu');");
            $this->execute("INSERT INTO `country` VALUES(129, 'MO', 'Macao', 'The Macao Special Administrative Region', 'MAC', '446', 'no', '853', '.mo');");
            $this->execute("INSERT INTO `country` VALUES(130, 'MK', 'Macedonia', 'The Former Yugoslav Republic of Macedonia', 'MKD', '807', 'yes', '389', '.mk');");
            $this->execute("INSERT INTO `country` VALUES(131, 'MG', 'Madagascar', 'Republic of Madagascar', 'MDG', '450', 'yes', '261', '.mg');");
            $this->execute("INSERT INTO `country` VALUES(132, 'MW', 'Malawi', 'Republic of Malawi', 'MWI', '454', 'yes', '265', '.mw');");
            $this->execute("INSERT INTO `country` VALUES(133, 'MY', 'Malaysia', 'Malaysia', 'MYS', '458', 'yes', '60', '.my');");
            $this->execute("INSERT INTO `country` VALUES(134, 'MV', 'Maldives', 'Republic of Maldives', 'MDV', '462', 'yes', '960', '.mv');");
            $this->execute("INSERT INTO `country` VALUES(135, 'ML', 'Mali', 'Republic of Mali', 'MLI', '466', 'yes', '223', '.ml');");
            $this->execute("INSERT INTO `country` VALUES(136, 'MT', 'Malta', 'Republic of Malta', 'MLT', '470', 'yes', '356', '.mt');");
            $this->execute("INSERT INTO `country` VALUES(137, 'MH', 'Marshall Islands', 'Republic of the Marshall Islands', 'MHL', '584', 'yes', '692', '.mh');");
            $this->execute("INSERT INTO `country` VALUES(138, 'MQ', 'Martinique', 'Martinique', 'MTQ', '474', 'no', '596', '.mq');");
            $this->execute("INSERT INTO `country` VALUES(139, 'MR', 'Mauritania', 'Islamic Republic of Mauritania', 'MRT', '478', 'yes', '222', '.mr');");
            $this->execute("INSERT INTO `country` VALUES(140, 'MU', 'Mauritius', 'Republic of Mauritius', 'MUS', '480', 'yes', '230', '.mu');");
            $this->execute("INSERT INTO `country` VALUES(141, 'YT', 'Mayotte', 'Mayotte', 'MYT', '175', 'no', '262', '.yt');");
            $this->execute("INSERT INTO `country` VALUES(142, 'MX', 'Mexico', 'United Mexican States', 'MEX', '484', 'yes', '52', '.mx');");
            $this->execute("INSERT INTO `country` VALUES(143, 'FM', 'Micronesia', 'Federated States of Micronesia', 'FSM', '583', 'yes', '691', '.fm');");
            $this->execute("INSERT INTO `country` VALUES(144, 'MD', 'Moldava', 'Republic of Moldova', 'MDA', '498', 'yes', '373', '.md');");
            $this->execute("INSERT INTO `country` VALUES(145, 'MC', 'Monaco', 'Principality of Monaco', 'MCO', '492', 'yes', '377', '.mc');");
            $this->execute("INSERT INTO `country` VALUES(146, 'MN', 'Mongolia', 'Mongolia', 'MNG', '496', 'yes', '976', '.mn');");
            $this->execute("INSERT INTO `country` VALUES(147, 'ME', 'Montenegro', 'Montenegro', 'MNE', '499', 'yes', '382', '.me');");
            $this->execute("INSERT INTO `country` VALUES(148, 'MS', 'Montserrat', 'Montserrat', 'MSR', '500', 'no', '1+664', '.ms');");
            $this->execute("INSERT INTO `country` VALUES(149, 'MA', 'Morocco', 'Kingdom of Morocco', 'MAR', '504', 'yes', '212', '.ma');");
            $this->execute("INSERT INTO `country` VALUES(150, 'MZ', 'Mozambique', 'Republic of Mozambique', 'MOZ', '508', 'yes', '258', '.mz');");
            $this->execute("INSERT INTO `country` VALUES(151, 'MM', 'Myanmar (Burma)', 'Republic of the Union of Myanmar', 'MMR', '104', 'yes', '95', '.mm');");
            $this->execute("INSERT INTO `country` VALUES(152, 'NA', 'Namibia', 'Republic of Namibia', 'NAM', '516', 'yes', '264', '.na');");
            $this->execute("INSERT INTO `country` VALUES(153, 'NR', 'Nauru', 'Republic of Nauru', 'NRU', '520', 'yes', '674', '.nr');");
            $this->execute("INSERT INTO `country` VALUES(154, 'NP', 'Nepal', 'Federal Democratic Republic of Nepal', 'NPL', '524', 'yes', '977', '.np');");
            $this->execute("INSERT INTO `country` VALUES(155, 'NL', 'Netherlands', 'Kingdom of the Netherlands', 'NLD', '528', 'yes', '31', '.nl');");
            $this->execute("INSERT INTO `country` VALUES(156, 'NC', 'New Caledonia', 'New Caledonia', 'NCL', '540', 'no', '687', '.nc');");
            $this->execute("INSERT INTO `country` VALUES(157, 'NZ', 'New Zealand', 'New Zealand', 'NZL', '554', 'yes', '64', '.nz');");
            $this->execute("INSERT INTO `country` VALUES(158, 'NI', 'Nicaragua', 'Republic of Nicaragua', 'NIC', '558', 'yes', '505', '.ni');");
            $this->execute("INSERT INTO `country` VALUES(159, 'NE', 'Niger', 'Republic of Niger', 'NER', '562', 'yes', '227', '.ne');");
            $this->execute("INSERT INTO `country` VALUES(160, 'NG', 'Nigeria', 'Federal Republic of Nigeria', 'NGA', '566', 'yes', '234', '.ng');");
            $this->execute("INSERT INTO `country` VALUES(161, 'NU', 'Niue', 'Niue', 'NIU', '570', 'some', '683', '.nu');");
            $this->execute("INSERT INTO `country` VALUES(162, 'NF', 'Norfolk Island', 'Norfolk Island', 'NFK', '574', 'no', '672', '.nf');");
            $this->execute("INSERT INTO `country` VALUES(163, 'KP', 'North Korea', 'Democratic People''s Republic of Korea', 'PRK', '408', 'yes', '850', '.kp');");
            $this->execute("INSERT INTO `country` VALUES(164, 'MP', 'Northern Mariana Islands', 'Northern Mariana Islands', 'MNP', '580', 'no', '1+670', '.mp');");
            $this->execute("INSERT INTO `country` VALUES(165, 'NO', 'Norway', 'Kingdom of Norway', 'NOR', '578', 'yes', '47', '.no');");
            $this->execute("INSERT INTO `country` VALUES(166, 'OM', 'Oman', 'Sultanate of Oman', 'OMN', '512', 'yes', '968', '.om');");
            $this->execute("INSERT INTO `country` VALUES(167, 'PK', 'Pakistan', 'Islamic Republic of Pakistan', 'PAK', '586', 'yes', '92', '.pk');");
            $this->execute("INSERT INTO `country` VALUES(168, 'PW', 'Palau', 'Republic of Palau', 'PLW', '585', 'yes', '680', '.pw');");
            $this->execute("INSERT INTO `country` VALUES(169, 'PS', 'Palestine', 'State of Palestine (or Occupied Palestinian Territory)', 'PSE', '275', 'some', '970', '.ps');");
            $this->execute("INSERT INTO `country` VALUES(170, 'PA', 'Panama', 'Republic of Panama', 'PAN', '591', 'yes', '507', '.pa');");
            $this->execute("INSERT INTO `country` VALUES(171, 'PG', 'Papua New Guinea', 'Independent State of Papua New Guinea', 'PNG', '598', 'yes', '675', '.pg');");
            $this->execute("INSERT INTO `country` VALUES(172, 'PY', 'Paraguay', 'Republic of Paraguay', 'PRY', '600', 'yes', '595', '.py');");
            $this->execute("INSERT INTO `country` VALUES(173, 'PE', 'Peru', 'Republic of Peru', 'PER', '604', 'yes', '51', '.pe');");
            $this->execute("INSERT INTO `country` VALUES(174, 'PH', 'Phillipines', 'Republic of the Philippines', 'PHL', '608', 'yes', '63', '.ph');");
            $this->execute("INSERT INTO `country` VALUES(175, 'PN', 'Pitcairn', 'Pitcairn', 'PCN', '612', 'no', 'NONE', '.pn');");
            $this->execute("INSERT INTO `country` VALUES(176, 'PL', 'Poland', 'Republic of Poland', 'POL', '616', 'yes', '48', '.pl');");
            $this->execute("INSERT INTO `country` VALUES(177, 'PT', 'Portugal', 'Portuguese Republic', 'PRT', '620', 'yes', '351', '.pt');");
            $this->execute("INSERT INTO `country` VALUES(178, 'PR', 'Puerto Rico', 'Commonwealth of Puerto Rico', 'PRI', '630', 'no', '1+939', '.pr');");
            $this->execute("INSERT INTO `country` VALUES(179, 'QA', 'Qatar', 'State of Qatar', 'QAT', '634', 'yes', '974', '.qa');");
            $this->execute("INSERT INTO `country` VALUES(180, 'RE', 'Reunion', 'R&eacute;union', 'REU', '638', 'no', '262', '.re');");
            $this->execute("INSERT INTO `country` VALUES(181, 'RO', 'Romania', 'Romania', 'ROU', '642', 'yes', '40', '.ro');");
            $this->execute("INSERT INTO `country` VALUES(182, 'RU', 'Russia', 'Russian Federation', 'RUS', '643', 'yes', '7', '.ru');");
            $this->execute("INSERT INTO `country` VALUES(183, 'RW', 'Rwanda', 'Republic of Rwanda', 'RWA', '646', 'yes', '250', '.rw');");
            $this->execute("INSERT INTO `country` VALUES(184, 'BL', 'Saint Barthelemy', 'Saint Barth&eacute;lemy', 'BLM', '652', 'no', '590', '.bl');");
            $this->execute("INSERT INTO `country` VALUES(185, 'SH', 'Saint Helena', 'Saint Helena, Ascension and Tristan da Cunha', 'SHN', '654', 'no', '290', '.sh');");
            $this->execute("INSERT INTO `country` VALUES(186, 'KN', 'Saint Kitts and Nevis', 'Federation of Saint Christopher and Nevis', 'KNA', '659', 'yes', '1+869', '.kn');");
            $this->execute("INSERT INTO `country` VALUES(187, 'LC', 'Saint Lucia', 'Saint Lucia', 'LCA', '662', 'yes', '1+758', '.lc');");
            $this->execute("INSERT INTO `country` VALUES(188, 'MF', 'Saint Martin', 'Saint Martin', 'MAF', '663', 'no', '590', '.mf');");
            $this->execute("INSERT INTO `country` VALUES(189, 'PM', 'Saint Pierre and Miquelon', 'Saint Pierre and Miquelon', 'SPM', '666', 'no', '508', '.pm');");
            $this->execute("INSERT INTO `country` VALUES(190, 'VC', 'Saint Vincent and the Grenadines', 'Saint Vincent and the Grenadines', 'VCT', '670', 'yes', '1+784', '.vc');");
            $this->execute("INSERT INTO `country` VALUES(191, 'WS', 'Samoa', 'Independent State of Samoa', 'WSM', '882', 'yes', '685', '.ws');");
            $this->execute("INSERT INTO `country` VALUES(192, 'SM', 'San Marino', 'Republic of San Marino', 'SMR', '674', 'yes', '378', '.sm');");
            $this->execute("INSERT INTO `country` VALUES(193, 'ST', 'Sao Tome and Principe', 'Democratic Republic of S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'STP', '678', 'yes', '239', '.st');");
            $this->execute("INSERT INTO `country` VALUES(194, 'SA', 'Saudi Arabia', 'Kingdom of Saudi Arabia', 'SAU', '682', 'yes', '966', '.sa');");
            $this->execute("INSERT INTO `country` VALUES(195, 'SN', 'Senegal', 'Republic of Senegal', 'SEN', '686', 'yes', '221', '.sn');");
            $this->execute("INSERT INTO `country` VALUES(196, 'RS', 'Serbia', 'Republic of Serbia', 'SRB', '688', 'yes', '381', '.rs');");
            $this->execute("INSERT INTO `country` VALUES(197, 'SC', 'Seychelles', 'Republic of Seychelles', 'SYC', '690', 'yes', '248', '.sc');");
            $this->execute("INSERT INTO `country` VALUES(198, 'SL', 'Sierra Leone', 'Republic of Sierra Leone', 'SLE', '694', 'yes', '232', '.sl');");
            $this->execute("INSERT INTO `country` VALUES(199, 'SG', 'Singapore', 'Republic of Singapore', 'SGP', '702', 'yes', '65', '.sg');");
            $this->execute("INSERT INTO `country` VALUES(200, 'SX', 'Sint Maarten', 'Sint Maarten', 'SXM', '534', 'no', '1+721', '.sx');");
            $this->execute("INSERT INTO `country` VALUES(201, 'SK', 'Slovakia', 'Slovak Republic', 'SVK', '703', 'yes', '421', '.sk');");
            $this->execute("INSERT INTO `country` VALUES(202, 'SI', 'Slovenia', 'Republic of Slovenia', 'SVN', '705', 'yes', '386', '.si');");
            $this->execute("INSERT INTO `country` VALUES(203, 'SB', 'Solomon Islands', 'Solomon Islands', 'SLB', '090', 'yes', '677', '.sb');");
            $this->execute("INSERT INTO `country` VALUES(204, 'SO', 'Somalia', 'Somali Republic', 'SOM', '706', 'yes', '252', '.so');");
            $this->execute("INSERT INTO `country` VALUES(205, 'ZA', 'South Africa', 'Republic of South Africa', 'ZAF', '710', 'yes', '27', '.za');");
            $this->execute("INSERT INTO `country` VALUES(206, 'GS', 'South Georgia and the South Sandwich Islands', 'South Georgia and the South Sandwich Islands', 'SGS', '239', 'no', '500', '.gs');");
            $this->execute("INSERT INTO `country` VALUES(207, 'KR', 'South Korea', 'Republic of Korea', 'KOR', '410', 'yes', '82', '.kr');");
            $this->execute("INSERT INTO `country` VALUES(208, 'SS', 'South Sudan', 'Republic of South Sudan', 'SSD', '728', 'yes', '211', '.ss');");
            $this->execute("INSERT INTO `country` VALUES(209, 'ES', 'Spain', 'Kingdom of Spain', 'ESP', '724', 'yes', '34', '.es');");
            $this->execute("INSERT INTO `country` VALUES(210, 'LK', 'Sri Lanka', 'Democratic Socialist Republic of Sri Lanka', 'LKA', '144', 'yes', '94', '.lk');");
            $this->execute("INSERT INTO `country` VALUES(211, 'SD', 'Sudan', 'Republic of the Sudan', 'SDN', '729', 'yes', '249', '.sd');");
            $this->execute("INSERT INTO `country` VALUES(212, 'SR', 'Suriname', 'Republic of Suriname', 'SUR', '740', 'yes', '597', '.sr');");
            $this->execute("INSERT INTO `country` VALUES(213, 'SJ', 'Svalbard and Jan Mayen', 'Svalbard and Jan Mayen', 'SJM', '744', 'no', '47', '.sj');");
            $this->execute("INSERT INTO `country` VALUES(214, 'SZ', 'Swaziland', 'Kingdom of Swaziland', 'SWZ', '748', 'yes', '268', '.sz');");
            $this->execute("INSERT INTO `country` VALUES(215, 'SE', 'Sweden', 'Kingdom of Sweden', 'SWE', '752', 'yes', '46', '.se');");
            $this->execute("INSERT INTO `country` VALUES(216, 'CH', 'Switzerland', 'Swiss Confederation', 'CHE', '756', 'yes', '41', '.ch');");
            $this->execute("INSERT INTO `country` VALUES(217, 'SY', 'Syria', 'Syrian Arab Republic', 'SYR', '760', 'yes', '963', '.sy');");
            $this->execute("INSERT INTO `country` VALUES(218, 'TW', 'Taiwan', 'Republic of China (Taiwan)', 'TWN', '158', 'former', '886', '.tw');");
            $this->execute("INSERT INTO `country` VALUES(219, 'TJ', 'Tajikistan', 'Republic of Tajikistan', 'TJK', '762', 'yes', '992', '.tj');");
            $this->execute("INSERT INTO `country` VALUES(220, 'TZ', 'Tanzania', 'United Republic of Tanzania', 'TZA', '834', 'yes', '255', '.tz');");
            $this->execute("INSERT INTO `country` VALUES(221, 'TH', 'Thailand', 'Kingdom of Thailand', 'THA', '764', 'yes', '66', '.th');");
            $this->execute("INSERT INTO `country` VALUES(222, 'TL', 'Timor-Leste (East Timor)', 'Democratic Republic of Timor-Leste', 'TLS', '626', 'yes', '670', '.tl');");
            $this->execute("INSERT INTO `country` VALUES(223, 'TG', 'Togo', 'Togolese Republic', 'TGO', '768', 'yes', '228', '.tg');");
            $this->execute("INSERT INTO `country` VALUES(224, 'TK', 'Tokelau', 'Tokelau', 'TKL', '772', 'no', '690', '.tk');");
            $this->execute("INSERT INTO `country` VALUES(225, 'TO', 'Tonga', 'Kingdom of Tonga', 'TON', '776', 'yes', '676', '.to');");
            $this->execute("INSERT INTO `country` VALUES(226, 'TT', 'Trinidad and Tobago', 'Republic of Trinidad and Tobago', 'TTO', '780', 'yes', '1+868', '.tt');");
            $this->execute("INSERT INTO `country` VALUES(227, 'TN', 'Tunisia', 'Republic of Tunisia', 'TUN', '788', 'yes', '216', '.tn');");
            $this->execute("INSERT INTO `country` VALUES(228, 'TR', 'Turkey', 'Republic of Turkey', 'TUR', '792', 'yes', '90', '.tr');");
            $this->execute("INSERT INTO `country` VALUES(229, 'TM', 'Turkmenistan', 'Turkmenistan', 'TKM', '795', 'yes', '993', '.tm');");
            $this->execute("INSERT INTO `country` VALUES(230, 'TC', 'Turks and Caicos Islands', 'Turks and Caicos Islands', 'TCA', '796', 'no', '1+649', '.tc');");
            $this->execute("INSERT INTO `country` VALUES(231, 'TV', 'Tuvalu', 'Tuvalu', 'TUV', '798', 'yes', '688', '.tv');");
            $this->execute("INSERT INTO `country` VALUES(232, 'UG', 'Uganda', 'Republic of Uganda', 'UGA', '800', 'yes', '256', '.ug');");
            $this->execute("INSERT INTO `country` VALUES(233, 'UA', 'Ukraine', 'Ukraine', 'UKR', '804', 'yes', '380', '.ua');");
            $this->execute("INSERT INTO `country` VALUES(234, 'AE', 'United Arab Emirates', 'United Arab Emirates', 'ARE', '784', 'yes', '971', '.ae');");
            $this->execute("INSERT INTO `country` VALUES(235, 'GB', 'United Kingdom', 'United Kingdom of Great Britain and Nothern Ireland', 'GBR', '826', 'yes', '44', '.uk');");
            $this->execute("INSERT INTO `country` VALUES(236, 'US', 'United States', 'United States of America', 'USA', '840', 'yes', '1', '.us');");
            $this->execute("INSERT INTO `country` VALUES(237, 'UM', 'United States Minor Outlying Islands', 'United States Minor Outlying Islands', 'UMI', '581', 'no', 'NONE', 'NONE');");
            $this->execute("INSERT INTO `country` VALUES(238, 'UY', 'Uruguay', 'Eastern Republic of Uruguay', 'URY', '858', 'yes', '598', '.uy');");
            $this->execute("INSERT INTO `country` VALUES(239, 'UZ', 'Uzbekistan', 'Republic of Uzbekistan', 'UZB', '860', 'yes', '998', '.uz');");
            $this->execute("INSERT INTO `country` VALUES(240, 'VU', 'Vanuatu', 'Republic of Vanuatu', 'VUT', '548', 'yes', '678', '.vu');");
            $this->execute("INSERT INTO `country` VALUES(241, 'VA', 'Vatican City', 'State of the Vatican City', 'VAT', '336', 'no', '39', '.va');");
            $this->execute("INSERT INTO `country` VALUES(242, 'VE', 'Venezuela', 'Bolivarian Republic of Venezuela', 'VEN', '862', 'yes', '58', '.ve');");
            $this->execute("INSERT INTO `country` VALUES(243, 'VN', 'Vietnam', 'Socialist Republic of Vietnam', 'VNM', '704', 'yes', '84', '.vn');");
            $this->execute("INSERT INTO `country` VALUES(244, 'VG', 'Virgin Islands, British', 'British Virgin Islands', 'VGB', '092', 'no', '1+284', '.vg');");
            $this->execute("INSERT INTO `country` VALUES(245, 'VI', 'Virgin Islands, US', 'Virgin Islands of the United States', 'VIR', '850', 'no', '1+340', '.vi');");
            $this->execute("INSERT INTO `country` VALUES(246, 'WF', 'Wallis and Futuna', 'Wallis and Futuna', 'WLF', '876', 'no', '681', '.wf');");
            $this->execute("INSERT INTO `country` VALUES(247, 'EH', 'Western Sahara', 'Western Sahara', 'ESH', '732', 'no', '212', '.eh');");
            $this->execute("INSERT INTO `country` VALUES(248, 'YE', 'Yemen', 'Republic of Yemen', 'YEM', '887', 'yes', '967', '.ye');");
            $this->execute("INSERT INTO `country` VALUES(249, 'ZM', 'Zambia', 'Republic of Zambia', 'ZMB', '894', 'yes', '260', '.zm');");
            $this->execute("INSERT INTO `country` VALUES(250, 'ZW', 'Zimbabwe', 'Republic of Zimbabwe', 'ZWE', '716', 'yes', '263', '.zw');");
        endif;

        // Migration for table error
        if (!$this->hasTable('error')) :
            $table = $this->table('error', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('type', 'integer', array('limit' => 4))
                ->addColumn('time', 'integer', array('limit' => 10))
                ->addColumn('string', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_LONG])
                ->addColumn('file', 'string', array('limit' => 191))
                ->addColumn('line', 'integer', array('limit' => 6))
                ->addColumn('addDate', 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'))
                ->create();
        endif;

        // Migration for table list
        if (!$this->hasTable('list')) :
            $table = $this->table('list', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('code', 'string', array('limit' => 191))
                ->addColumn('name', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('description', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_MEDIUM])
                ->addColumn('created', 'datetime', ['null' => true])
                ->addColumn('owner', 'integer', array('limit' => MysqlAdapter::INT_REGULAR))
                ->addColumn('redirect_success', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('redirect_unsuccess', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('confirm_email', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
                ->addColumn('subscribe_email', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
                ->addColumn('unsubscribe_email', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
                ->addColumn('optin', 'integer', array('default' => '0', 'limit' => 1))
                ->addColumn('status', 'enum', array('default' => 'open', 'values' => array('open', 'closed')))
                ->addColumn('LastUpdate', 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'))
                ->addForeignKey('owner', 'user', 'id', array('constraint' => 'list_owner', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->create();
        endif;

        // Migration for table options
        if (!$this->hasTable('options')) :
            $table = $this->table('options', ['id' => false, 'primary_key' => 'meta_id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('meta_id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('meta_key', 'string', array('limit' => 191))
                ->addColumn('meta_value', 'text', array('null' => true, 'limit' => MysqlAdapter::TEXT_LONG))
                ->addIndex(['meta_key'], ['unique' => true])
                ->create();

            $this->execute("
            INSERT INTO `options` VALUES(1, 'system_name', 'tinyCampaign');
            INSERT INTO `options` VALUES(2, 'system_email', 'no-reply@tc.com');
            INSERT INTO `options` VALUES(3, 'cookieexpire', '604800');
            INSERT INTO `options` VALUES(4, 'cookiepath', '/');
            INSERT INTO `options` VALUES(5, 'mail_throttle', '0.5');
            INSERT INTO `options` VALUES(6, 'tc_smtp_host', '');
            INSERT INTO `options` VALUES(7, 'tc_smtp_username', '');
            INSERT INTO `options` VALUES(8, 'tc_smtp_password', '');
            INSERT INTO `options` VALUES(9, 'tc_smtp_port', '');
            INSERT INTO `options` VALUES(10, 'tc_smtp_smtpsecure', '');
            INSERT INTO `options` VALUES(11, 'tc_smtp_status', '0');
            INSERT INTO `options` VALUES(12, 'backend_skin', 'skin-blue');
            INSERT INTO `options` VALUES(13, 'tc_core_locale', 'en_US');
            INSERT INTO `options` VALUES(14, 'system_timezone', 'America/New_York');
            INSERT INTO `options` VALUES(15, 'api_key', '" . $api_key . "');
            INSERT INTO `options` VALUES(16, 'mailing_address', '');
            INSERT INTO `options` VALUES(17, 'tc_bmh_host', '');
            INSERT INTO `options` VALUES(18, 'tc_bmh_username', '');
            INSERT INTO `options` VALUES(19, 'tc_bmh_password', '');
            INSERT INTO `options` VALUES(20, 'tc_bmh_mailbox', '');
            INSERT INTO `options` VALUES(21, 'tc_bmh_port', '');
            INSERT INTO `options` VALUES(22, 'tc_bmh_service', '');
            INSERT INTO `options` VALUES(23, 'tc_bmh_service_option', '');
            INSERT INTO `options` VALUES(24, 'collapse_sidebar', 'no');
        ");
        endif;

        // Migration for table permission
        if (!$this->hasTable('permission')) :
            $table = $this->table('permission', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['signed' => false, 'identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('permKey', 'string', array('limit' => 191))
                ->addColumn('permName', 'string', array('limit' => 191))
                ->create();
            
            $this->execute("
            INSERT INTO `permission` VALUES(1, 'access_permission_screen', 'Access Permission Screen');
            INSERT INTO `permission` VALUES(2, 'access_role_screen', 'Access Role Screen');
            INSERT INTO `permission` VALUES(3, 'access_dashboard', 'Access Dashboard');
            INSERT INTO `permission` VALUES(4, 'access_settings_screen', 'Access Settings Screen');
            INSERT INTO `permission` VALUES(5, 'create_email_list', 'Create Email List');
            INSERT INTO `permission` VALUES(6, 'manage_email_lists', 'Manage Email Lists');
            INSERT INTO `permission` VALUES(7, 'manage_campaigns', 'Manage Campaigns');
            INSERT INTO `permission` VALUES(8, 'create_campaign', 'Create Campaigns');
            INSERT INTO `permission` VALUES(9, 'manage_users', 'Manage Users');
            INSERT INTO `permission` VALUES(10, 'edit_user', 'Edit User');
            INSERT INTO `permission` VALUES(11, 'delete_user', 'Delete User');
            INSERT INTO `permission` VALUES(12, 'user_inquiry_only', 'User Inquiry Only');
            INSERT INTO `permission` VALUES(13, 'create_user', 'Create User');
            INSERT INTO `permission` VALUES(14, 'edit_campaign', 'Edit Campaign');
            INSERT INTO `permission` VALUES(15, 'delete_campaign', 'Delete Campaign');
            INSERT INTO `permission` VALUES(16, 'edit_email_list', 'Edit Email List');
            INSERT INTO `permission` VALUES(17, 'delete_email_list', 'Delete Email List');
            INSERT INTO `permission` VALUES(18, 'manage_subscribers', 'Manage Subscribers');
            INSERT INTO `permission` VALUES(19, 'add_subscriber', 'Add Subscriber');
            INSERT INTO `permission` VALUES(20, 'edit_subscriber', 'Edit Subscriber');
            INSERT INTO `permission` VALUES(21, 'delete_subscriber', 'Delete Subscriber');
            INSERT INTO `permission` VALUES(22, 'subscriber_inquiry_only', 'Subscriber Inquiry Only');
            INSERT INTO `permission` VALUES(23, 'campaign_inquiry_only', 'Campaign Inquiry Only');
            INSERT INTO `permission` VALUES(24, 'email_list_inquiry_only', 'Email List Inquiry Only');
            INSERT INTO `permission` VALUES(25, 'access_cronjob_screen', 'Access Cronjob Handler Screen');
            INSERT INTO `permission` VALUES(26, 'manage_plugins', 'Manage Plugins');
            INSERT INTO `permission` VALUES(27, 'install_plugins', 'Install Plugins');
            INSERT INTO `permission` VALUES(28, 'activate_deactivate_plugins', 'Activate/Deactivate Plugins');
            INSERT INTO `permission` VALUES(29, 'switch_user', 'Switch User');
        ");
        endif;

        // Migration for table plugin
        if (!$this->hasTable('plugin')) :
            $table = $this->table('plugin', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_REGULAR])
                ->addColumn('location', 'text', [])
                ->create();
        endif;

        // Migration for table role
        if (!$this->hasTable('role')) :
            $table = $this->table('role', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['signed' => false, 'identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('roleName', 'string', array('limit' => 80))
                ->addColumn('permission', 'text', array('null' => true, 'limit' => MysqlAdapter::TEXT_LONG))
                ->create();
            
            $this->execute("
            INSERT INTO `role` VALUES(1, 'Super Admin', 'a:25:{i:0;s:24:\"access_permission_screen\";i:1;s:18:\"access_role_screen\";i:2;s:16:\"access_dashboard\";i:3;s:22:\"access_settings_screen\";i:4;s:17:\"create_email_list\";i:5;s:18:\"manage_email_lists\";i:6;s:16:\"manage_campaigns\";i:7;s:15:\"create_campaign\";i:8;s:12:\"manage_users\";i:9;s:9:\"edit_user\";i:10;s:11:\"delete_user\";i:11;s:11:\"create_user\";i:12;s:13:\"edit_campaign\";i:13;s:15:\"delete_campaign\";i:14;s:15:\"edit_email_list\";i:15;s:17:\"delete_email_list\";i:16;s:18:\"manage_subscribers\";i:17;s:14:\"add_subscriber\";i:18;s:15:\"edit_subscriber\";i:19;s:17:\"delete_subscriber\";i:20;s:21:\"access_cronjob_screen\";i:21;s:14:\"manage_plugins\";i:22;s:15:\"install_plugins\";i:23;s:27:\"activate_deactivate_plugins\";i:24;s:11:\"switch_user\";}');
        ");
        endif;

        // Migration for table role_perms
        if (!$this->hasTable('role_perms')) :
            $table = $this->table('role_perms', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['signed' => false, 'identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('roleID', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_BIG))
                ->addColumn('permID', 'integer', array('signed' => false, 'limit' => MysqlAdapter::INT_BIG))
                ->addColumn('value', 'integer', array('default' => '0', 'limit' => MysqlAdapter::INT_TINY))
                ->addColumn('addDate', 'datetime', [])
                ->addForeignKey('roleID', 'role', 'id', array('constraint' => 'role_perms_roleID', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->addForeignKey('permID', 'permission', 'id', array('constraint' => 'role_perms_permID', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->create();
        endif;

        // Migration for table state
        if (!$this->hasTable('state')) :
            $table = $this->table('state', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_REGULAR])
                ->addColumn('code', 'char', array('limit' => 8))
                ->addColumn('name', 'string', array('limit' => 191))
                ->addIndex(['code'], ['unique' => true])
                ->create();

            $this->execute("INSERT INTO `state` VALUES(1, 'AL', 'Alabama');");
            $this->execute("INSERT INTO `state` VALUES(2, 'AK', 'Alaska');");
            $this->execute("INSERT INTO `state` VALUES(3, 'AZ', 'Arizona');");
            $this->execute("INSERT INTO `state` VALUES(4, 'AR', 'Arkansas');");
            $this->execute("INSERT INTO `state` VALUES(5, 'CA', 'California');");
            $this->execute("INSERT INTO `state` VALUES(6, 'CO', 'Colorado');");
            $this->execute("INSERT INTO `state` VALUES(7, 'CT', 'Connecticut');");
            $this->execute("INSERT INTO `state` VALUES(8, 'DE', 'Delaware');");
            $this->execute("INSERT INTO `state` VALUES(9, 'DC', 'District of Columbia');");
            $this->execute("INSERT INTO `state` VALUES(10, 'FL', 'Florida');");
            $this->execute("INSERT INTO `state` VALUES(11, 'GA', 'Georgia');");
            $this->execute("INSERT INTO `state` VALUES(12, 'HI', 'Hawaii');");
            $this->execute("INSERT INTO `state` VALUES(13, 'ID', 'Idaho');");
            $this->execute("INSERT INTO `state` VALUES(14, 'IL', 'Illinois');");
            $this->execute("INSERT INTO `state` VALUES(15, 'IN', 'Indiana');");
            $this->execute("INSERT INTO `state` VALUES(16, 'IA', 'Iowa');");
            $this->execute("INSERT INTO `state` VALUES(17, 'KS', 'Kansas');");
            $this->execute("INSERT INTO `state` VALUES(18, 'KY', 'Kentucky');");
            $this->execute("INSERT INTO `state` VALUES(19, 'LA', 'Louisiana');");
            $this->execute("INSERT INTO `state` VALUES(20, 'ME', 'Maine');");
            $this->execute("INSERT INTO `state` VALUES(21, 'MD', 'Maryland');");
            $this->execute("INSERT INTO `state` VALUES(22, 'MA', 'Massachusetts');");
            $this->execute("INSERT INTO `state` VALUES(23, 'MI', 'Michigan');");
            $this->execute("INSERT INTO `state` VALUES(24, 'MN', 'Minnesota');");
            $this->execute("INSERT INTO `state` VALUES(25, 'MS', 'Mississippi');");
            $this->execute("INSERT INTO `state` VALUES(26, 'MO', 'Missouri');");
            $this->execute("INSERT INTO `state` VALUES(27, 'MT', 'Montana');");
            $this->execute("INSERT INTO `state` VALUES(28, 'NE', 'Nebraska');");
            $this->execute("INSERT INTO `state` VALUES(29, 'NV', 'Nevada');");
            $this->execute("INSERT INTO `state` VALUES(30, 'NH', 'New Hampshire');");
            $this->execute("INSERT INTO `state` VALUES(31, 'NJ', 'New Jersey');");
            $this->execute("INSERT INTO `state` VALUES(32, 'NM', 'New Mexico');");
            $this->execute("INSERT INTO `state` VALUES(33, 'NY', 'New York');");
            $this->execute("INSERT INTO `state` VALUES(34, 'NC', 'North Carolina');");
            $this->execute("INSERT INTO `state` VALUES(35, 'ND', 'North Dakota');");
            $this->execute("INSERT INTO `state` VALUES(36, 'OH', 'Ohio');");
            $this->execute("INSERT INTO `state` VALUES(37, 'OK', 'Oklahoma');");
            $this->execute("INSERT INTO `state` VALUES(38, 'OR', 'Oregon');");
            $this->execute("INSERT INTO `state` VALUES(39, 'PA', 'Pennsylvania');");
            $this->execute("INSERT INTO `state` VALUES(40, 'RI', 'Rhode Island');");
            $this->execute("INSERT INTO `state` VALUES(41, 'SC', 'South Carolina');");
            $this->execute("INSERT INTO `state` VALUES(42, 'SD', 'South Dakota');");
            $this->execute("INSERT INTO `state` VALUES(43, 'TN', 'Tennessee');");
            $this->execute("INSERT INTO `state` VALUES(44, 'TX', 'Texas');");
            $this->execute("INSERT INTO `state` VALUES(45, 'UT', 'Utah');");
            $this->execute("INSERT INTO `state` VALUES(46, 'VT', 'Vermont');");
            $this->execute("INSERT INTO `state` VALUES(47, 'VA', 'Virginia');");
            $this->execute("INSERT INTO `state` VALUES(48, 'WA', 'Washington');");
            $this->execute("INSERT INTO `state` VALUES(49, 'WV', 'West Virginia');");
            $this->execute("INSERT INTO `state` VALUES(50, 'WI', 'Wisconsin');");
            $this->execute("INSERT INTO `state` VALUES(51, 'WY', 'Wyoming');");
            $this->execute("INSERT INTO `state` VALUES(52, 'AB', 'Alberta');");
            $this->execute("INSERT INTO `state` VALUES(53, 'BC', 'British Columbia');");
            $this->execute("INSERT INTO `state` VALUES(54, 'MB', 'Manitoba');");
            $this->execute("INSERT INTO `state` VALUES(55, 'NL', 'Newfoundland');");
            $this->execute("INSERT INTO `state` VALUES(56, 'NB', 'New Brunswick');");
            $this->execute("INSERT INTO `state` VALUES(57, 'NS', 'Nova Scotia');");
            $this->execute("INSERT INTO `state` VALUES(58, 'NT', 'Northwest Territories');");
            $this->execute("INSERT INTO `state` VALUES(59, 'NU', 'Nunavut');");
            $this->execute("INSERT INTO `state` VALUES(60, 'ON', 'Ontario');");
            $this->execute("INSERT INTO `state` VALUES(61, 'PE', 'Prince Edward Island');");
            $this->execute("INSERT INTO `state` VALUES(62, 'QC', 'Quebec');");
            $this->execute("INSERT INTO `state` VALUES(63, 'SK', 'Saskatchewan');");
            $this->execute("INSERT INTO `state` VALUES(64, 'YT', 'Yukon Territory');");
        endif;

        // Migration for table subscriber
        if (!$this->hasTable('subscriber')) :
            $table = $this->table('subscriber', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('fname', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('lname', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('email', 'string', array('limit' => 191))
                ->addColumn('address1', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('address2', 'string', array('null' => true, 'limit' => 22))
                ->addColumn('city', 'string', array('null' => true, 'limit' => 80))
                ->addColumn('state', 'char', array('null' => true, 'limit' => 8))
                ->addColumn('postal_code', 'string', array('null' => true, 'limit' => 22))
                ->addColumn('country', 'char', array('null' => true, 'limit' => 8))
                ->addColumn('code', 'text', ['limit' => MysqlAdapter::INT_REGULAR])
                ->addColumn('allowed', 'enum', array('default' => 'true', 'values' => array('true', 'false')))
                ->addColumn('bounces', 'integer', array('null' => true, 'default' => '0', 'limit' => 11))
                ->addColumn('ip', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('addedBy', 'integer', array('limit' => MysqlAdapter::INT_REGULAR))
                ->addColumn('addDate', 'datetime', [])
                ->addColumn('LastUpdate', 'datetime', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'))
                ->addForeignKey('addedBy', 'user', 'id', array('constraint' => 'subscriber_addedBy', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->addForeignKey('state', 'state', 'code', array('constraint' => 'subscriber_state', 'delete' => 'SET_NULL', 'update' => 'CASCADE'))
                ->create();
        endif;

        // Migration for table subscriber_list
        if (!$this->hasTable('subscriber_list')) :
            $table = $this->table('subscriber_list', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('lid', 'integer', array('limit' => MysqlAdapter::INT_BIG))
                ->addColumn('sid', 'integer', array('limit' => MysqlAdapter::INT_BIG))
                ->addColumn('addDate', 'datetime', ['null' => true])
                ->addColumn('code', 'text', ['limit' => MysqlAdapter::INT_REGULAR])
                ->addColumn('confirmed', 'enum', array('default' => '0', 'values' => array('1', '0')))
                ->addColumn('unsubscribe', 'enum', array('default' => '0', 'values' => array('1', '0')))
                ->addColumn('LastUpdate', 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'))
                ->addForeignKey('lid', 'list', 'id', array('constraint' => 'subscriber_list_lid', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->addForeignKey('sid', 'subscriber', 'id', array('constraint' => 'subscriber_list_sid', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->create();
        endif;

        // Migration for table tracking
        if (!$this->hasTable('tracking')) :
            $table = $this->table('tracking', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('cid', 'integer', array('limit' => MysqlAdapter::INT_BIG))
                ->addColumn('sid', 'integer', array('limit' => MysqlAdapter::INT_BIG))
                ->addColumn('first_open', 'datetime', array('null' => true))
                ->addColumn('viewed', 'integer', array('limit' => MysqlAdapter::INT_BIG))
                ->addColumn('LastUpdate', 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'))
                ->addForeignKey('cid', 'campaign', 'id', array('constraint' => 'tracking_cid', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->addForeignKey('sid', 'subscriber', 'id', array('constraint' => 'tracking_sid', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->create();
        endif;

        // Migration for table user
        if (!$this->hasTable('user')) :
            $table = $this->table('user', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_REGULAR])
                ->addColumn('fname', 'string', array('limit' => 191))
                ->addColumn('lname', 'string', array('limit' => 191))
                ->addColumn('uname', 'string', array('limit' => 191))
                ->addColumn('email', 'string', array('limit' => 191))
                ->addColumn('address1', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('address2', 'string', array('null' => true, 'limit' => 80))
                ->addColumn('city', 'string', array('null' => true, 'limit' => 191))
                ->addColumn('state', 'char', array('null' => true, 'limit' => 8))
                ->addColumn('postal_code', 'string', array('null' => true, 'limit' => 12))
                ->addColumn('country', 'char', array('null' => true, 'limit' => 8))
                ->addColumn('password', 'string', array('limit' => 191))
                ->addColumn('code', 'string', array('limit' => 191))
                ->addColumn('status', 'enum', array('default' => '1', 'values' => array('1', '0')))
                ->addColumn('roleID', 'integer', array('signed' => false, 'null' => true, 'limit' => MysqlAdapter::INT_BIG))
                ->addColumn('date_added', 'datetime', ['null' => true])
                ->addColumn('LastLogin', 'datetime', ['null' => true])
                ->addColumn('LastUpdate', 'datetime', [])
                ->addForeignKey('roleID', 'role', 'id', array('constraint' => 'user_roleID', 'delete' => 'SET_NULL', 'update' => 'CASCADE'))
                ->addForeignKey('state', 'state', 'code', array('constraint' => 'user_state', 'delete' => 'SET_NULL', 'update' => 'CASCADE'))
                ->create();
            
            $this->execute("
            INSERT INTO `user` VALUES(1, 'Tiny', 'Campaign', 'tinyc', 'tinyc@tc.com', '', '', 'Boston', 'MA', '02108', 'US', '$2a$08$0S4AFqozF/OXQtzkiR8/U.MJdaQmfUNVjR3fBJ3RqGWCxLkdIp1N2', '', '1', 1, '" . $NOW . "', '2017-02-01 08:27:29', '2017-01-31 03:32:25');
        ");
        endif;

        // Migration for table user_perms
        if (!$this->hasTable('user_perms')) :
            $table = $this->table('user_perms', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['signed' => false, 'identity' => true, 'limit' => MysqlAdapter::INT_BIG])
                ->addColumn('userID', 'integer', array('limit' => MysqlAdapter::INT_REGULAR))
                ->addColumn('permission', 'text', ['limit' => MysqlAdapter::TEXT_REGULAR])
                ->addColumn('LastUpdate', 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'))
                ->addForeignKey('userID', 'user', 'id', array('constraint' => 'user_perms_userID', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->create();
        endif;

        // Migration for table user_roles
        if (!$this->hasTable('user_roles')) :
            $table = $this->table('user_roles', ['id' => false, 'primary_key' => 'id', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
            $table
                ->addColumn('id', 'integer', ['identity' => true, 'limit' => MysqlAdapter::INT_REGULAR])
                ->addColumn('userID', 'integer', array('limit' => MysqlAdapter::INT_REGULAR))
                ->addColumn('roleID', 'integer', array('signed' => false, 'null' => true, 'limit' => MysqlAdapter::INT_BIG))
                ->addColumn('addDate', 'datetime', [])
                ->addForeignKey('roleID', 'role', 'id', array('constraint' => 'user_roles_roleID', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->addForeignKey('userID', 'user', 'id', array('constraint' => 'user_roles_userID', 'delete' => 'CASCADE', 'update' => 'CASCADE'))
                ->create();
            
            $this->execute("
            INSERT INTO `user_roles` VALUES(1, 1, 1, '" . $NOW . "');
        ");
        endif;

        // re-arm foreign key checks
        $this->execute('SET FOREIGN_KEY_CHECKS=1;');

        if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false && !file_exists('.htaccess')) {
            $f = fopen('.htaccess', 'w');
            fclose($f);

            $htaccess_file = <<<EOF
<IfModule mod_rewrite.c>
RewriteEngine On

# Some hosts may require you to use the `RewriteBase` directive.
# If you need to use the `RewriteBase` directive, it should be the
# absolute physical path to the directory that contains this htaccess file.
#
# RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
</IfModule>

EOF;
            file_put_contents('.htaccess', $htaccess_file);
        }

        if (!file_exists('config.php')) {
            copy('config.sample.php', 'config.php');
        }
        $file = 'config.php';
        $config = file_get_contents($file);

        $config = str_replace('{product}', 'tinyCampaign', $config);
        $config = str_replace('{release}', trim(file_get_contents('RELEASE')), $config);
        $config = str_replace('{datenow}', $NOW, $config);
        $config = str_replace('{hostname}', $host, $config);
        $config = str_replace('{database}', $name, $config);
        $config = str_replace('{username}', $user, $config);
        $config = str_replace('{password}', $pass, $config);

        file_put_contents($file, $config);
    }
}
