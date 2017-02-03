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

        // CREATE table string for table: "activity"
        $this->execute("
            CREATE TABLE `activity` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `action` varchar(50) NOT NULL,
              `process` varchar(255) NOT NULL,
              `record` longtext,
              `uname` varchar(180) NOT NULL,
              `created_at` datetime NOT NULL,
              `expires_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "campaign"
        $this->execute("
            CREATE TABLE `campaign` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `owner` int(11) NOT NULL,
              `node` varchar(80) NOT NULL,
              `subject` varchar(255) NOT NULL DEFAULT '',
              `from_name` varchar(255) NOT NULL,
              `from_email` varchar(150) NOT NULL,
              `html` text NOT NULL,
              `text` text,
              `footer` text,
              `attachment` text,
              `status` enum('ready','processing','paused','sent') NOT NULL DEFAULT 'ready',
              `sendstart` datetime NOT NULL,
              `sendfinish` datetime DEFAULT NULL,
              `recipients` int(11) NOT NULL DEFAULT '0',
              `viewed` int(11) NOT NULL DEFAULT '0',
              `bounces` int(11) NOT NULL DEFAULT '0',
              `archive` enum('1','0') NOT NULL DEFAULT '0',
              `addDate` datetime NOT NULL,
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `owner` (`owner`),
              FULLTEXT KEY `subject` (`subject`,`html`),
              CONSTRAINT `campaign_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "campaign_list"
        $this->execute("
            CREATE TABLE `campaign_list` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `cid` bigint(20) NOT NULL,
              `lid` bigint(20) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `cid` (`cid`),
              KEY `lid` (`lid`),
              CONSTRAINT `campaign_list_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `campaign_list_ibfk_2` FOREIGN KEY (`lid`) REFERENCES `list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "country"
        $this->execute("
            CREATE TABLE `country` (
              `id` int(5) NOT NULL AUTO_INCREMENT,
              `iso2` char(2) DEFAULT NULL,
              `short_name` varchar(80) NOT NULL DEFAULT '',
              `long_name` varchar(80) NOT NULL DEFAULT '',
              `iso3` char(3) DEFAULT NULL,
              `numcode` varchar(6) DEFAULT NULL,
              `un_member` varchar(12) DEFAULT NULL,
              `calling_code` varchar(8) DEFAULT NULL,
              `cctld` varchar(5) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // INSERT data string for table: "country"
        $this->execute("
            INSERT INTO `country` VALUES(1, 'AF', 'Afghanistan', 'Islamic Republic of Afghanistan', 'AFG', '004', 'yes', '93', '.af');
INSERT INTO `country` VALUES(2, 'AX', 'Aland Islands', '&Aring;land Islands', 'ALA', '248', 'no', '358', '.ax');
INSERT INTO `country` VALUES(3, 'AL', 'Albania', 'Republic of Albania', 'ALB', '008', 'yes', '355', '.al');
INSERT INTO `country` VALUES(4, 'DZ', 'Algeria', 'People\'s Democratic Republic of Algeria', 'DZA', '012', 'yes', '213', '.dz');
INSERT INTO `country` VALUES(5, 'AS', 'American Samoa', 'American Samoa', 'ASM', '016', 'no', '1+684', '.as');
INSERT INTO `country` VALUES(6, 'AD', 'Andorra', 'Principality of Andorra', 'AND', '020', 'yes', '376', '.ad');
INSERT INTO `country` VALUES(7, 'AO', 'Angola', 'Republic of Angola', 'AGO', '024', 'yes', '244', '.ao');
INSERT INTO `country` VALUES(8, 'AI', 'Anguilla', 'Anguilla', 'AIA', '660', 'no', '1+264', '.ai');
INSERT INTO `country` VALUES(9, 'AQ', 'Antarctica', 'Antarctica', 'ATA', '010', 'no', '672', '.aq');
INSERT INTO `country` VALUES(10, 'AG', 'Antigua and Barbuda', 'Antigua and Barbuda', 'ATG', '028', 'yes', '1+268', '.ag');
INSERT INTO `country` VALUES(11, 'AR', 'Argentina', 'Argentine Republic', 'ARG', '032', 'yes', '54', '.ar');
INSERT INTO `country` VALUES(12, 'AM', 'Armenia', 'Republic of Armenia', 'ARM', '051', 'yes', '374', '.am');
INSERT INTO `country` VALUES(13, 'AW', 'Aruba', 'Aruba', 'ABW', '533', 'no', '297', '.aw');
INSERT INTO `country` VALUES(14, 'AU', 'Australia', 'Commonwealth of Australia', 'AUS', '036', 'yes', '61', '.au');
INSERT INTO `country` VALUES(15, 'AT', 'Austria', 'Republic of Austria', 'AUT', '040', 'yes', '43', '.at');
INSERT INTO `country` VALUES(16, 'AZ', 'Azerbaijan', 'Republic of Azerbaijan', 'AZE', '031', 'yes', '994', '.az');
INSERT INTO `country` VALUES(17, 'BS', 'Bahamas', 'Commonwealth of The Bahamas', 'BHS', '044', 'yes', '1+242', '.bs');
INSERT INTO `country` VALUES(18, 'BH', 'Bahrain', 'Kingdom of Bahrain', 'BHR', '048', 'yes', '973', '.bh');
INSERT INTO `country` VALUES(19, 'BD', 'Bangladesh', 'People\'s Republic of Bangladesh', 'BGD', '050', 'yes', '880', '.bd');
INSERT INTO `country` VALUES(20, 'BB', 'Barbados', 'Barbados', 'BRB', '052', 'yes', '1+246', '.bb');
INSERT INTO `country` VALUES(21, 'BY', 'Belarus', 'Republic of Belarus', 'BLR', '112', 'yes', '375', '.by');
INSERT INTO `country` VALUES(22, 'BE', 'Belgium', 'Kingdom of Belgium', 'BEL', '056', 'yes', '32', '.be');
INSERT INTO `country` VALUES(23, 'BZ', 'Belize', 'Belize', 'BLZ', '084', 'yes', '501', '.bz');
INSERT INTO `country` VALUES(24, 'BJ', 'Benin', 'Republic of Benin', 'BEN', '204', 'yes', '229', '.bj');
INSERT INTO `country` VALUES(25, 'BM', 'Bermuda', 'Bermuda Islands', 'BMU', '060', 'no', '1+441', '.bm');
INSERT INTO `country` VALUES(26, 'BT', 'Bhutan', 'Kingdom of Bhutan', 'BTN', '064', 'yes', '975', '.bt');
INSERT INTO `country` VALUES(27, 'BO', 'Bolivia', 'Plurinational State of Bolivia', 'BOL', '068', 'yes', '591', '.bo');
INSERT INTO `country` VALUES(28, 'BQ', 'Bonaire, Sint Eustatius and Saba', 'Bonaire, Sint Eustatius and Saba', 'BES', '535', 'no', '599', '.bq');
INSERT INTO `country` VALUES(29, 'BA', 'Bosnia and Herzegovina', 'Bosnia and Herzegovina', 'BIH', '070', 'yes', '387', '.ba');
INSERT INTO `country` VALUES(30, 'BW', 'Botswana', 'Republic of Botswana', 'BWA', '072', 'yes', '267', '.bw');
INSERT INTO `country` VALUES(31, 'BV', 'Bouvet Island', 'Bouvet Island', 'BVT', '074', 'no', 'NONE', '.bv');
INSERT INTO `country` VALUES(32, 'BR', 'Brazil', 'Federative Republic of Brazil', 'BRA', '076', 'yes', '55', '.br');
INSERT INTO `country` VALUES(33, 'IO', 'British Indian Ocean Territory', 'British Indian Ocean Territory', 'IOT', '086', 'no', '246', '.io');
INSERT INTO `country` VALUES(34, 'BN', 'Brunei', 'Brunei Darussalam', 'BRN', '096', 'yes', '673', '.bn');
INSERT INTO `country` VALUES(35, 'BG', 'Bulgaria', 'Republic of Bulgaria', 'BGR', '100', 'yes', '359', '.bg');
INSERT INTO `country` VALUES(36, 'BF', 'Burkina Faso', 'Burkina Faso', 'BFA', '854', 'yes', '226', '.bf');
INSERT INTO `country` VALUES(37, 'BI', 'Burundi', 'Republic of Burundi', 'BDI', '108', 'yes', '257', '.bi');
INSERT INTO `country` VALUES(38, 'KH', 'Cambodia', 'Kingdom of Cambodia', 'KHM', '116', 'yes', '855', '.kh');
INSERT INTO `country` VALUES(39, 'CM', 'Cameroon', 'Republic of Cameroon', 'CMR', '120', 'yes', '237', '.cm');
INSERT INTO `country` VALUES(40, 'CA', 'Canada', 'Canada', 'CAN', '124', 'yes', '1', '.ca');
INSERT INTO `country` VALUES(41, 'CV', 'Cape Verde', 'Republic of Cape Verde', 'CPV', '132', 'yes', '238', '.cv');
INSERT INTO `country` VALUES(42, 'KY', 'Cayman Islands', 'The Cayman Islands', 'CYM', '136', 'no', '1+345', '.ky');
INSERT INTO `country` VALUES(43, 'CF', 'Central African Republic', 'Central African Republic', 'CAF', '140', 'yes', '236', '.cf');
INSERT INTO `country` VALUES(44, 'TD', 'Chad', 'Republic of Chad', 'TCD', '148', 'yes', '235', '.td');
INSERT INTO `country` VALUES(45, 'CL', 'Chile', 'Republic of Chile', 'CHL', '152', 'yes', '56', '.cl');
INSERT INTO `country` VALUES(46, 'CN', 'China', 'People\'s Republic of China', 'CHN', '156', 'yes', '86', '.cn');
INSERT INTO `country` VALUES(47, 'CX', 'Christmas Island', 'Christmas Island', 'CXR', '162', 'no', '61', '.cx');
INSERT INTO `country` VALUES(48, 'CC', 'Cocos (Keeling) Islands', 'Cocos (Keeling) Islands', 'CCK', '166', 'no', '61', '.cc');
INSERT INTO `country` VALUES(49, 'CO', 'Colombia', 'Republic of Colombia', 'COL', '170', 'yes', '57', '.co');
INSERT INTO `country` VALUES(50, 'KM', 'Comoros', 'Union of the Comoros', 'COM', '174', 'yes', '269', '.km');
INSERT INTO `country` VALUES(51, 'CG', 'Congo', 'Republic of the Congo', 'COG', '178', 'yes', '242', '.cg');
INSERT INTO `country` VALUES(52, 'CK', 'Cook Islands', 'Cook Islands', 'COK', '184', 'some', '682', '.ck');
INSERT INTO `country` VALUES(53, 'CR', 'Costa Rica', 'Republic of Costa Rica', 'CRI', '188', 'yes', '506', '.cr');
INSERT INTO `country` VALUES(54, 'CI', 'Cote d\'ivoire (Ivory Coast)', 'Republic of C&ocirc;te D\'Ivoire (Ivory Coast)', 'CIV', '384', 'yes', '225', '.ci');
INSERT INTO `country` VALUES(55, 'HR', 'Croatia', 'Republic of Croatia', 'HRV', '191', 'yes', '385', '.hr');
INSERT INTO `country` VALUES(56, 'CU', 'Cuba', 'Republic of Cuba', 'CUB', '192', 'yes', '53', '.cu');
INSERT INTO `country` VALUES(57, 'CW', 'Curacao', 'Cura&ccedil;ao', 'CUW', '531', 'no', '599', '.cw');
INSERT INTO `country` VALUES(58, 'CY', 'Cyprus', 'Republic of Cyprus', 'CYP', '196', 'yes', '357', '.cy');
INSERT INTO `country` VALUES(59, 'CZ', 'Czech Republic', 'Czech Republic', 'CZE', '203', 'yes', '420', '.cz');
INSERT INTO `country` VALUES(60, 'CD', 'Democratic Republic of the Congo', 'Democratic Republic of the Congo', 'COD', '180', 'yes', '243', '.cd');
INSERT INTO `country` VALUES(61, 'DK', 'Denmark', 'Kingdom of Denmark', 'DNK', '208', 'yes', '45', '.dk');
INSERT INTO `country` VALUES(62, 'DJ', 'Djibouti', 'Republic of Djibouti', 'DJI', '262', 'yes', '253', '.dj');
INSERT INTO `country` VALUES(63, 'DM', 'Dominica', 'Commonwealth of Dominica', 'DMA', '212', 'yes', '1+767', '.dm');
INSERT INTO `country` VALUES(64, 'DO', 'Dominican Republic', 'Dominican Republic', 'DOM', '214', 'yes', '1+809, 8', '.do');
INSERT INTO `country` VALUES(65, 'EC', 'Ecuador', 'Republic of Ecuador', 'ECU', '218', 'yes', '593', '.ec');
INSERT INTO `country` VALUES(66, 'EG', 'Egypt', 'Arab Republic of Egypt', 'EGY', '818', 'yes', '20', '.eg');
INSERT INTO `country` VALUES(67, 'SV', 'El Salvador', 'Republic of El Salvador', 'SLV', '222', 'yes', '503', '.sv');
INSERT INTO `country` VALUES(68, 'GQ', 'Equatorial Guinea', 'Republic of Equatorial Guinea', 'GNQ', '226', 'yes', '240', '.gq');
INSERT INTO `country` VALUES(69, 'ER', 'Eritrea', 'State of Eritrea', 'ERI', '232', 'yes', '291', '.er');
INSERT INTO `country` VALUES(70, 'EE', 'Estonia', 'Republic of Estonia', 'EST', '233', 'yes', '372', '.ee');
INSERT INTO `country` VALUES(71, 'ET', 'Ethiopia', 'Federal Democratic Republic of Ethiopia', 'ETH', '231', 'yes', '251', '.et');
INSERT INTO `country` VALUES(72, 'FK', 'Falkland Islands (Malvinas)', 'The Falkland Islands (Malvinas)', 'FLK', '238', 'no', '500', '.fk');
INSERT INTO `country` VALUES(73, 'FO', 'Faroe Islands', 'The Faroe Islands', 'FRO', '234', 'no', '298', '.fo');
INSERT INTO `country` VALUES(74, 'FJ', 'Fiji', 'Republic of Fiji', 'FJI', '242', 'yes', '679', '.fj');
INSERT INTO `country` VALUES(75, 'FI', 'Finland', 'Republic of Finland', 'FIN', '246', 'yes', '358', '.fi');
INSERT INTO `country` VALUES(76, 'FR', 'France', 'French Republic', 'FRA', '250', 'yes', '33', '.fr');
INSERT INTO `country` VALUES(77, 'GF', 'French Guiana', 'French Guiana', 'GUF', '254', 'no', '594', '.gf');
INSERT INTO `country` VALUES(78, 'PF', 'French Polynesia', 'French Polynesia', 'PYF', '258', 'no', '689', '.pf');
INSERT INTO `country` VALUES(79, 'TF', 'French Southern Territories', 'French Southern Territories', 'ATF', '260', 'no', NULL, '.tf');
INSERT INTO `country` VALUES(80, 'GA', 'Gabon', 'Gabonese Republic', 'GAB', '266', 'yes', '241', '.ga');
INSERT INTO `country` VALUES(81, 'GM', 'Gambia', 'Republic of The Gambia', 'GMB', '270', 'yes', '220', '.gm');
INSERT INTO `country` VALUES(82, 'GE', 'Georgia', 'Georgia', 'GEO', '268', 'yes', '995', '.ge');
INSERT INTO `country` VALUES(83, 'DE', 'Germany', 'Federal Republic of Germany', 'DEU', '276', 'yes', '49', '.de');
INSERT INTO `country` VALUES(84, 'GH', 'Ghana', 'Republic of Ghana', 'GHA', '288', 'yes', '233', '.gh');
INSERT INTO `country` VALUES(85, 'GI', 'Gibraltar', 'Gibraltar', 'GIB', '292', 'no', '350', '.gi');
INSERT INTO `country` VALUES(86, 'GR', 'Greece', 'Hellenic Republic', 'GRC', '300', 'yes', '30', '.gr');
INSERT INTO `country` VALUES(87, 'GL', 'Greenland', 'Greenland', 'GRL', '304', 'no', '299', '.gl');
INSERT INTO `country` VALUES(88, 'GD', 'Grenada', 'Grenada', 'GRD', '308', 'yes', '1+473', '.gd');
INSERT INTO `country` VALUES(89, 'GP', 'Guadaloupe', 'Guadeloupe', 'GLP', '312', 'no', '590', '.gp');
INSERT INTO `country` VALUES(90, 'GU', 'Guam', 'Guam', 'GUM', '316', 'no', '1+671', '.gu');
INSERT INTO `country` VALUES(91, 'GT', 'Guatemala', 'Republic of Guatemala', 'GTM', '320', 'yes', '502', '.gt');
INSERT INTO `country` VALUES(92, 'GG', 'Guernsey', 'Guernsey', 'GGY', '831', 'no', '44', '.gg');
INSERT INTO `country` VALUES(93, 'GN', 'Guinea', 'Republic of Guinea', 'GIN', '324', 'yes', '224', '.gn');
INSERT INTO `country` VALUES(94, 'GW', 'Guinea-Bissau', 'Republic of Guinea-Bissau', 'GNB', '624', 'yes', '245', '.gw');
INSERT INTO `country` VALUES(95, 'GY', 'Guyana', 'Co-operative Republic of Guyana', 'GUY', '328', 'yes', '592', '.gy');
INSERT INTO `country` VALUES(96, 'HT', 'Haiti', 'Republic of Haiti', 'HTI', '332', 'yes', '509', '.ht');
INSERT INTO `country` VALUES(97, 'HM', 'Heard Island and McDonald Islands', 'Heard Island and McDonald Islands', 'HMD', '334', 'no', 'NONE', '.hm');
INSERT INTO `country` VALUES(98, 'HN', 'Honduras', 'Republic of Honduras', 'HND', '340', 'yes', '504', '.hn');
INSERT INTO `country` VALUES(99, 'HK', 'Hong Kong', 'Hong Kong', 'HKG', '344', 'no', '852', '.hk');
INSERT INTO `country` VALUES(100, 'HU', 'Hungary', 'Hungary', 'HUN', '348', 'yes', '36', '.hu');
INSERT INTO `country` VALUES(101, 'IS', 'Iceland', 'Republic of Iceland', 'ISL', '352', 'yes', '354', '.is');
INSERT INTO `country` VALUES(102, 'IN', 'India', 'Republic of India', 'IND', '356', 'yes', '91', '.in');
INSERT INTO `country` VALUES(103, 'ID', 'Indonesia', 'Republic of Indonesia', 'IDN', '360', 'yes', '62', '.id');
INSERT INTO `country` VALUES(104, 'IR', 'Iran', 'Islamic Republic of Iran', 'IRN', '364', 'yes', '98', '.ir');
INSERT INTO `country` VALUES(105, 'IQ', 'Iraq', 'Republic of Iraq', 'IRQ', '368', 'yes', '964', '.iq');
INSERT INTO `country` VALUES(106, 'IE', 'Ireland', 'Ireland', 'IRL', '372', 'yes', '353', '.ie');
INSERT INTO `country` VALUES(107, 'IM', 'Isle of Man', 'Isle of Man', 'IMN', '833', 'no', '44', '.im');
INSERT INTO `country` VALUES(108, 'IL', 'Israel', 'State of Israel', 'ISR', '376', 'yes', '972', '.il');
INSERT INTO `country` VALUES(109, 'IT', 'Italy', 'Italian Republic', 'ITA', '380', 'yes', '39', '.jm');
INSERT INTO `country` VALUES(110, 'JM', 'Jamaica', 'Jamaica', 'JAM', '388', 'yes', '1+876', '.jm');
INSERT INTO `country` VALUES(111, 'JP', 'Japan', 'Japan', 'JPN', '392', 'yes', '81', '.jp');
INSERT INTO `country` VALUES(112, 'JE', 'Jersey', 'The Bailiwick of Jersey', 'JEY', '832', 'no', '44', '.je');
INSERT INTO `country` VALUES(113, 'JO', 'Jordan', 'Hashemite Kingdom of Jordan', 'JOR', '400', 'yes', '962', '.jo');
INSERT INTO `country` VALUES(114, 'KZ', 'Kazakhstan', 'Republic of Kazakhstan', 'KAZ', '398', 'yes', '7', '.kz');
INSERT INTO `country` VALUES(115, 'KE', 'Kenya', 'Republic of Kenya', 'KEN', '404', 'yes', '254', '.ke');
INSERT INTO `country` VALUES(116, 'KI', 'Kiribati', 'Republic of Kiribati', 'KIR', '296', 'yes', '686', '.ki');
INSERT INTO `country` VALUES(117, 'XK', 'Kosovo', 'Republic of Kosovo', '---', '---', 'some', '381', '');
INSERT INTO `country` VALUES(118, 'KW', 'Kuwait', 'State of Kuwait', 'KWT', '414', 'yes', '965', '.kw');
INSERT INTO `country` VALUES(119, 'KG', 'Kyrgyzstan', 'Kyrgyz Republic', 'KGZ', '417', 'yes', '996', '.kg');
INSERT INTO `country` VALUES(120, 'LA', 'Laos', 'Lao People\'s Democratic Republic', 'LAO', '418', 'yes', '856', '.la');
INSERT INTO `country` VALUES(121, 'LV', 'Latvia', 'Republic of Latvia', 'LVA', '428', 'yes', '371', '.lv');
INSERT INTO `country` VALUES(122, 'LB', 'Lebanon', 'Republic of Lebanon', 'LBN', '422', 'yes', '961', '.lb');
INSERT INTO `country` VALUES(123, 'LS', 'Lesotho', 'Kingdom of Lesotho', 'LSO', '426', 'yes', '266', '.ls');
INSERT INTO `country` VALUES(124, 'LR', 'Liberia', 'Republic of Liberia', 'LBR', '430', 'yes', '231', '.lr');
INSERT INTO `country` VALUES(125, 'LY', 'Libya', 'Libya', 'LBY', '434', 'yes', '218', '.ly');
INSERT INTO `country` VALUES(126, 'LI', 'Liechtenstein', 'Principality of Liechtenstein', 'LIE', '438', 'yes', '423', '.li');
INSERT INTO `country` VALUES(127, 'LT', 'Lithuania', 'Republic of Lithuania', 'LTU', '440', 'yes', '370', '.lt');
INSERT INTO `country` VALUES(128, 'LU', 'Luxembourg', 'Grand Duchy of Luxembourg', 'LUX', '442', 'yes', '352', '.lu');
INSERT INTO `country` VALUES(129, 'MO', 'Macao', 'The Macao Special Administrative Region', 'MAC', '446', 'no', '853', '.mo');
INSERT INTO `country` VALUES(130, 'MK', 'Macedonia', 'The Former Yugoslav Republic of Macedonia', 'MKD', '807', 'yes', '389', '.mk');
INSERT INTO `country` VALUES(131, 'MG', 'Madagascar', 'Republic of Madagascar', 'MDG', '450', 'yes', '261', '.mg');
INSERT INTO `country` VALUES(132, 'MW', 'Malawi', 'Republic of Malawi', 'MWI', '454', 'yes', '265', '.mw');
INSERT INTO `country` VALUES(133, 'MY', 'Malaysia', 'Malaysia', 'MYS', '458', 'yes', '60', '.my');
INSERT INTO `country` VALUES(134, 'MV', 'Maldives', 'Republic of Maldives', 'MDV', '462', 'yes', '960', '.mv');
INSERT INTO `country` VALUES(135, 'ML', 'Mali', 'Republic of Mali', 'MLI', '466', 'yes', '223', '.ml');
INSERT INTO `country` VALUES(136, 'MT', 'Malta', 'Republic of Malta', 'MLT', '470', 'yes', '356', '.mt');
INSERT INTO `country` VALUES(137, 'MH', 'Marshall Islands', 'Republic of the Marshall Islands', 'MHL', '584', 'yes', '692', '.mh');
INSERT INTO `country` VALUES(138, 'MQ', 'Martinique', 'Martinique', 'MTQ', '474', 'no', '596', '.mq');
INSERT INTO `country` VALUES(139, 'MR', 'Mauritania', 'Islamic Republic of Mauritania', 'MRT', '478', 'yes', '222', '.mr');
INSERT INTO `country` VALUES(140, 'MU', 'Mauritius', 'Republic of Mauritius', 'MUS', '480', 'yes', '230', '.mu');
INSERT INTO `country` VALUES(141, 'YT', 'Mayotte', 'Mayotte', 'MYT', '175', 'no', '262', '.yt');
INSERT INTO `country` VALUES(142, 'MX', 'Mexico', 'United Mexican States', 'MEX', '484', 'yes', '52', '.mx');
INSERT INTO `country` VALUES(143, 'FM', 'Micronesia', 'Federated States of Micronesia', 'FSM', '583', 'yes', '691', '.fm');
INSERT INTO `country` VALUES(144, 'MD', 'Moldava', 'Republic of Moldova', 'MDA', '498', 'yes', '373', '.md');
INSERT INTO `country` VALUES(145, 'MC', 'Monaco', 'Principality of Monaco', 'MCO', '492', 'yes', '377', '.mc');
INSERT INTO `country` VALUES(146, 'MN', 'Mongolia', 'Mongolia', 'MNG', '496', 'yes', '976', '.mn');
INSERT INTO `country` VALUES(147, 'ME', 'Montenegro', 'Montenegro', 'MNE', '499', 'yes', '382', '.me');
INSERT INTO `country` VALUES(148, 'MS', 'Montserrat', 'Montserrat', 'MSR', '500', 'no', '1+664', '.ms');
INSERT INTO `country` VALUES(149, 'MA', 'Morocco', 'Kingdom of Morocco', 'MAR', '504', 'yes', '212', '.ma');
INSERT INTO `country` VALUES(150, 'MZ', 'Mozambique', 'Republic of Mozambique', 'MOZ', '508', 'yes', '258', '.mz');
INSERT INTO `country` VALUES(151, 'MM', 'Myanmar (Burma)', 'Republic of the Union of Myanmar', 'MMR', '104', 'yes', '95', '.mm');
INSERT INTO `country` VALUES(152, 'NA', 'Namibia', 'Republic of Namibia', 'NAM', '516', 'yes', '264', '.na');
INSERT INTO `country` VALUES(153, 'NR', 'Nauru', 'Republic of Nauru', 'NRU', '520', 'yes', '674', '.nr');
INSERT INTO `country` VALUES(154, 'NP', 'Nepal', 'Federal Democratic Republic of Nepal', 'NPL', '524', 'yes', '977', '.np');
INSERT INTO `country` VALUES(155, 'NL', 'Netherlands', 'Kingdom of the Netherlands', 'NLD', '528', 'yes', '31', '.nl');
INSERT INTO `country` VALUES(156, 'NC', 'New Caledonia', 'New Caledonia', 'NCL', '540', 'no', '687', '.nc');
INSERT INTO `country` VALUES(157, 'NZ', 'New Zealand', 'New Zealand', 'NZL', '554', 'yes', '64', '.nz');
INSERT INTO `country` VALUES(158, 'NI', 'Nicaragua', 'Republic of Nicaragua', 'NIC', '558', 'yes', '505', '.ni');
INSERT INTO `country` VALUES(159, 'NE', 'Niger', 'Republic of Niger', 'NER', '562', 'yes', '227', '.ne');
INSERT INTO `country` VALUES(160, 'NG', 'Nigeria', 'Federal Republic of Nigeria', 'NGA', '566', 'yes', '234', '.ng');
INSERT INTO `country` VALUES(161, 'NU', 'Niue', 'Niue', 'NIU', '570', 'some', '683', '.nu');
INSERT INTO `country` VALUES(162, 'NF', 'Norfolk Island', 'Norfolk Island', 'NFK', '574', 'no', '672', '.nf');
INSERT INTO `country` VALUES(163, 'KP', 'North Korea', 'Democratic People\'s Republic of Korea', 'PRK', '408', 'yes', '850', '.kp');
INSERT INTO `country` VALUES(164, 'MP', 'Northern Mariana Islands', 'Northern Mariana Islands', 'MNP', '580', 'no', '1+670', '.mp');
INSERT INTO `country` VALUES(165, 'NO', 'Norway', 'Kingdom of Norway', 'NOR', '578', 'yes', '47', '.no');
INSERT INTO `country` VALUES(166, 'OM', 'Oman', 'Sultanate of Oman', 'OMN', '512', 'yes', '968', '.om');
INSERT INTO `country` VALUES(167, 'PK', 'Pakistan', 'Islamic Republic of Pakistan', 'PAK', '586', 'yes', '92', '.pk');
INSERT INTO `country` VALUES(168, 'PW', 'Palau', 'Republic of Palau', 'PLW', '585', 'yes', '680', '.pw');
INSERT INTO `country` VALUES(169, 'PS', 'Palestine', 'State of Palestine (or Occupied Palestinian Territory)', 'PSE', '275', 'some', '970', '.ps');
INSERT INTO `country` VALUES(170, 'PA', 'Panama', 'Republic of Panama', 'PAN', '591', 'yes', '507', '.pa');
INSERT INTO `country` VALUES(171, 'PG', 'Papua New Guinea', 'Independent State of Papua New Guinea', 'PNG', '598', 'yes', '675', '.pg');
INSERT INTO `country` VALUES(172, 'PY', 'Paraguay', 'Republic of Paraguay', 'PRY', '600', 'yes', '595', '.py');
INSERT INTO `country` VALUES(173, 'PE', 'Peru', 'Republic of Peru', 'PER', '604', 'yes', '51', '.pe');
INSERT INTO `country` VALUES(174, 'PH', 'Phillipines', 'Republic of the Philippines', 'PHL', '608', 'yes', '63', '.ph');
INSERT INTO `country` VALUES(175, 'PN', 'Pitcairn', 'Pitcairn', 'PCN', '612', 'no', 'NONE', '.pn');
INSERT INTO `country` VALUES(176, 'PL', 'Poland', 'Republic of Poland', 'POL', '616', 'yes', '48', '.pl');
INSERT INTO `country` VALUES(177, 'PT', 'Portugal', 'Portuguese Republic', 'PRT', '620', 'yes', '351', '.pt');
INSERT INTO `country` VALUES(178, 'PR', 'Puerto Rico', 'Commonwealth of Puerto Rico', 'PRI', '630', 'no', '1+939', '.pr');
INSERT INTO `country` VALUES(179, 'QA', 'Qatar', 'State of Qatar', 'QAT', '634', 'yes', '974', '.qa');
INSERT INTO `country` VALUES(180, 'RE', 'Reunion', 'R&eacute;union', 'REU', '638', 'no', '262', '.re');
INSERT INTO `country` VALUES(181, 'RO', 'Romania', 'Romania', 'ROU', '642', 'yes', '40', '.ro');
INSERT INTO `country` VALUES(182, 'RU', 'Russia', 'Russian Federation', 'RUS', '643', 'yes', '7', '.ru');
INSERT INTO `country` VALUES(183, 'RW', 'Rwanda', 'Republic of Rwanda', 'RWA', '646', 'yes', '250', '.rw');
INSERT INTO `country` VALUES(184, 'BL', 'Saint Barthelemy', 'Saint Barth&eacute;lemy', 'BLM', '652', 'no', '590', '.bl');
INSERT INTO `country` VALUES(185, 'SH', 'Saint Helena', 'Saint Helena, Ascension and Tristan da Cunha', 'SHN', '654', 'no', '290', '.sh');
INSERT INTO `country` VALUES(186, 'KN', 'Saint Kitts and Nevis', 'Federation of Saint Christopher and Nevis', 'KNA', '659', 'yes', '1+869', '.kn');
INSERT INTO `country` VALUES(187, 'LC', 'Saint Lucia', 'Saint Lucia', 'LCA', '662', 'yes', '1+758', '.lc');
INSERT INTO `country` VALUES(188, 'MF', 'Saint Martin', 'Saint Martin', 'MAF', '663', 'no', '590', '.mf');
INSERT INTO `country` VALUES(189, 'PM', 'Saint Pierre and Miquelon', 'Saint Pierre and Miquelon', 'SPM', '666', 'no', '508', '.pm');
INSERT INTO `country` VALUES(190, 'VC', 'Saint Vincent and the Grenadines', 'Saint Vincent and the Grenadines', 'VCT', '670', 'yes', '1+784', '.vc');
INSERT INTO `country` VALUES(191, 'WS', 'Samoa', 'Independent State of Samoa', 'WSM', '882', 'yes', '685', '.ws');
INSERT INTO `country` VALUES(192, 'SM', 'San Marino', 'Republic of San Marino', 'SMR', '674', 'yes', '378', '.sm');
INSERT INTO `country` VALUES(193, 'ST', 'Sao Tome and Principe', 'Democratic Republic of S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'STP', '678', 'yes', '239', '.st');
INSERT INTO `country` VALUES(194, 'SA', 'Saudi Arabia', 'Kingdom of Saudi Arabia', 'SAU', '682', 'yes', '966', '.sa');
INSERT INTO `country` VALUES(195, 'SN', 'Senegal', 'Republic of Senegal', 'SEN', '686', 'yes', '221', '.sn');
INSERT INTO `country` VALUES(196, 'RS', 'Serbia', 'Republic of Serbia', 'SRB', '688', 'yes', '381', '.rs');
INSERT INTO `country` VALUES(197, 'SC', 'Seychelles', 'Republic of Seychelles', 'SYC', '690', 'yes', '248', '.sc');
INSERT INTO `country` VALUES(198, 'SL', 'Sierra Leone', 'Republic of Sierra Leone', 'SLE', '694', 'yes', '232', '.sl');
INSERT INTO `country` VALUES(199, 'SG', 'Singapore', 'Republic of Singapore', 'SGP', '702', 'yes', '65', '.sg');
INSERT INTO `country` VALUES(200, 'SX', 'Sint Maarten', 'Sint Maarten', 'SXM', '534', 'no', '1+721', '.sx');
INSERT INTO `country` VALUES(201, 'SK', 'Slovakia', 'Slovak Republic', 'SVK', '703', 'yes', '421', '.sk');
INSERT INTO `country` VALUES(202, 'SI', 'Slovenia', 'Republic of Slovenia', 'SVN', '705', 'yes', '386', '.si');
INSERT INTO `country` VALUES(203, 'SB', 'Solomon Islands', 'Solomon Islands', 'SLB', '090', 'yes', '677', '.sb');
INSERT INTO `country` VALUES(204, 'SO', 'Somalia', 'Somali Republic', 'SOM', '706', 'yes', '252', '.so');
INSERT INTO `country` VALUES(205, 'ZA', 'South Africa', 'Republic of South Africa', 'ZAF', '710', 'yes', '27', '.za');
INSERT INTO `country` VALUES(206, 'GS', 'South Georgia and the South Sandwich Islands', 'South Georgia and the South Sandwich Islands', 'SGS', '239', 'no', '500', '.gs');
INSERT INTO `country` VALUES(207, 'KR', 'South Korea', 'Republic of Korea', 'KOR', '410', 'yes', '82', '.kr');
INSERT INTO `country` VALUES(208, 'SS', 'South Sudan', 'Republic of South Sudan', 'SSD', '728', 'yes', '211', '.ss');
INSERT INTO `country` VALUES(209, 'ES', 'Spain', 'Kingdom of Spain', 'ESP', '724', 'yes', '34', '.es');
INSERT INTO `country` VALUES(210, 'LK', 'Sri Lanka', 'Democratic Socialist Republic of Sri Lanka', 'LKA', '144', 'yes', '94', '.lk');
INSERT INTO `country` VALUES(211, 'SD', 'Sudan', 'Republic of the Sudan', 'SDN', '729', 'yes', '249', '.sd');
INSERT INTO `country` VALUES(212, 'SR', 'Suriname', 'Republic of Suriname', 'SUR', '740', 'yes', '597', '.sr');
INSERT INTO `country` VALUES(213, 'SJ', 'Svalbard and Jan Mayen', 'Svalbard and Jan Mayen', 'SJM', '744', 'no', '47', '.sj');
INSERT INTO `country` VALUES(214, 'SZ', 'Swaziland', 'Kingdom of Swaziland', 'SWZ', '748', 'yes', '268', '.sz');
INSERT INTO `country` VALUES(215, 'SE', 'Sweden', 'Kingdom of Sweden', 'SWE', '752', 'yes', '46', '.se');
INSERT INTO `country` VALUES(216, 'CH', 'Switzerland', 'Swiss Confederation', 'CHE', '756', 'yes', '41', '.ch');
INSERT INTO `country` VALUES(217, 'SY', 'Syria', 'Syrian Arab Republic', 'SYR', '760', 'yes', '963', '.sy');
INSERT INTO `country` VALUES(218, 'TW', 'Taiwan', 'Republic of China (Taiwan)', 'TWN', '158', 'former', '886', '.tw');
INSERT INTO `country` VALUES(219, 'TJ', 'Tajikistan', 'Republic of Tajikistan', 'TJK', '762', 'yes', '992', '.tj');
INSERT INTO `country` VALUES(220, 'TZ', 'Tanzania', 'United Republic of Tanzania', 'TZA', '834', 'yes', '255', '.tz');
INSERT INTO `country` VALUES(221, 'TH', 'Thailand', 'Kingdom of Thailand', 'THA', '764', 'yes', '66', '.th');
INSERT INTO `country` VALUES(222, 'TL', 'Timor-Leste (East Timor)', 'Democratic Republic of Timor-Leste', 'TLS', '626', 'yes', '670', '.tl');
INSERT INTO `country` VALUES(223, 'TG', 'Togo', 'Togolese Republic', 'TGO', '768', 'yes', '228', '.tg');
INSERT INTO `country` VALUES(224, 'TK', 'Tokelau', 'Tokelau', 'TKL', '772', 'no', '690', '.tk');
INSERT INTO `country` VALUES(225, 'TO', 'Tonga', 'Kingdom of Tonga', 'TON', '776', 'yes', '676', '.to');
INSERT INTO `country` VALUES(226, 'TT', 'Trinidad and Tobago', 'Republic of Trinidad and Tobago', 'TTO', '780', 'yes', '1+868', '.tt');
INSERT INTO `country` VALUES(227, 'TN', 'Tunisia', 'Republic of Tunisia', 'TUN', '788', 'yes', '216', '.tn');
INSERT INTO `country` VALUES(228, 'TR', 'Turkey', 'Republic of Turkey', 'TUR', '792', 'yes', '90', '.tr');
INSERT INTO `country` VALUES(229, 'TM', 'Turkmenistan', 'Turkmenistan', 'TKM', '795', 'yes', '993', '.tm');
INSERT INTO `country` VALUES(230, 'TC', 'Turks and Caicos Islands', 'Turks and Caicos Islands', 'TCA', '796', 'no', '1+649', '.tc');
INSERT INTO `country` VALUES(231, 'TV', 'Tuvalu', 'Tuvalu', 'TUV', '798', 'yes', '688', '.tv');
INSERT INTO `country` VALUES(232, 'UG', 'Uganda', 'Republic of Uganda', 'UGA', '800', 'yes', '256', '.ug');
INSERT INTO `country` VALUES(233, 'UA', 'Ukraine', 'Ukraine', 'UKR', '804', 'yes', '380', '.ua');
INSERT INTO `country` VALUES(234, 'AE', 'United Arab Emirates', 'United Arab Emirates', 'ARE', '784', 'yes', '971', '.ae');
INSERT INTO `country` VALUES(235, 'GB', 'United Kingdom', 'United Kingdom of Great Britain and Nothern Ireland', 'GBR', '826', 'yes', '44', '.uk');
INSERT INTO `country` VALUES(236, 'US', 'United States', 'United States of America', 'USA', '840', 'yes', '1', '.us');
INSERT INTO `country` VALUES(237, 'UM', 'United States Minor Outlying Islands', 'United States Minor Outlying Islands', 'UMI', '581', 'no', 'NONE', 'NONE');
INSERT INTO `country` VALUES(238, 'UY', 'Uruguay', 'Eastern Republic of Uruguay', 'URY', '858', 'yes', '598', '.uy');
INSERT INTO `country` VALUES(239, 'UZ', 'Uzbekistan', 'Republic of Uzbekistan', 'UZB', '860', 'yes', '998', '.uz');
INSERT INTO `country` VALUES(240, 'VU', 'Vanuatu', 'Republic of Vanuatu', 'VUT', '548', 'yes', '678', '.vu');
INSERT INTO `country` VALUES(241, 'VA', 'Vatican City', 'State of the Vatican City', 'VAT', '336', 'no', '39', '.va');
INSERT INTO `country` VALUES(242, 'VE', 'Venezuela', 'Bolivarian Republic of Venezuela', 'VEN', '862', 'yes', '58', '.ve');
INSERT INTO `country` VALUES(243, 'VN', 'Vietnam', 'Socialist Republic of Vietnam', 'VNM', '704', 'yes', '84', '.vn');
INSERT INTO `country` VALUES(244, 'VG', 'Virgin Islands, British', 'British Virgin Islands', 'VGB', '092', 'no', '1+284', '.vg');
INSERT INTO `country` VALUES(245, 'VI', 'Virgin Islands, US', 'Virgin Islands of the United States', 'VIR', '850', 'no', '1+340', '.vi');
INSERT INTO `country` VALUES(246, 'WF', 'Wallis and Futuna', 'Wallis and Futuna', 'WLF', '876', 'no', '681', '.wf');
INSERT INTO `country` VALUES(247, 'EH', 'Western Sahara', 'Western Sahara', 'ESH', '732', 'no', '212', '.eh');
INSERT INTO `country` VALUES(248, 'YE', 'Yemen', 'Republic of Yemen', 'YEM', '887', 'yes', '967', '.ye');
INSERT INTO `country` VALUES(249, 'ZM', 'Zambia', 'Republic of Zambia', 'ZMB', '894', 'yes', '260', '.zm');
INSERT INTO `country` VALUES(250, 'ZW', 'Zimbabwe', 'Republic of Zimbabwe', 'ZWE', '716', 'yes', '263', '.zw');
        ");

        // CREATE table string for table: "error"
        $this->execute("
            CREATE TABLE `error` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `type` int(4) NOT NULL,
              `time` int(10) NOT NULL,
              `string` varchar(512) NOT NULL,
              `file` varchar(255) NOT NULL,
              `line` int(6) NOT NULL,
              `addDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "list"
        $this->execute("
            CREATE TABLE `list` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `code` varchar(180) NOT NULL,
              `name` varchar(255) NOT NULL DEFAULT '',
              `description` text NOT NULL,
              `created` datetime NOT NULL,
              `owner` int(11) NOT NULL,
              `redirect_success` varchar(255) DEFAULT NULL,
              `redirect_unsuccess` varchar(255) DEFAULT NULL,
              `confirm_email` mediumtext NOT NULL,
              `subscribe_email` mediumtext NOT NULL,
              `unsubscribe_email` mediumtext NOT NULL,
              `optin` int(1) NOT NULL DEFAULT '0',
              `status` enum('open','closed') NOT NULL DEFAULT 'open',
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "options"
        $this->execute("
            CREATE TABLE `options` (
              `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `meta_key` varchar(64) NOT NULL DEFAULT '',
              `meta_value` longtext NOT NULL,
              PRIMARY KEY (`meta_id`),
              UNIQUE KEY `option_name` (`meta_key`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // INSERT data string for table: "options"
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
INSERT INTO `options` VALUES(15, 'api_key', '".$api_key."');
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

        // CREATE table string for table: "permission"
        $this->execute("
            CREATE TABLE `permission` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `permKey` varchar(30) NOT NULL,
              `permName` varchar(80) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `permKey` (`permKey`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // INSERT data string for table: "permission"
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

        // CREATE table string for table: "plugin"
        $this->execute("
            CREATE TABLE `plugin` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `location` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "role"
        $this->execute("
            CREATE TABLE `role` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `roleName` varchar(20) NOT NULL,
              `permission` longtext NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `roleName` (`roleName`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // INSERT data string for table: "role"
        $this->execute("
            INSERT INTO `role` VALUES(1, 'Super Admin', 'a:25:{i:0;s:24:\"access_permission_screen\";i:1;s:18:\"access_role_screen\";i:2;s:16:\"access_dashboard\";i:3;s:22:\"access_settings_screen\";i:4;s:17:\"create_email_list\";i:5;s:18:\"manage_email_lists\";i:6;s:16:\"manage_campaigns\";i:7;s:15:\"create_campaign\";i:8;s:12:\"manage_users\";i:9;s:9:\"edit_user\";i:10;s:11:\"delete_user\";i:11;s:11:\"create_user\";i:12;s:13:\"edit_campaign\";i:13;s:15:\"delete_campaign\";i:14;s:15:\"edit_email_list\";i:15;s:17:\"delete_email_list\";i:16;s:18:\"manage_subscribers\";i:17;s:14:\"add_subscriber\";i:18;s:15:\"edit_subscriber\";i:19;s:17:\"delete_subscriber\";i:20;s:21:\"access_cronjob_screen\";i:21;s:14:\"manage_plugins\";i:22;s:15:\"install_plugins\";i:23;s:27:\"activate_deactivate_plugins\";i:24;s:11:\"switch_user\";}');
        ");

        // CREATE table string for table: "role_perms"
        $this->execute("
            CREATE TABLE `role_perms` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `roleID` bigint(20) NOT NULL,
              `permID` bigint(20) NOT NULL,
              `value` tinyint(4) NOT NULL DEFAULT '0',
              `addDate` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `roleID` (`roleID`,`permID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "state"
        $this->execute("
            CREATE TABLE `state` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `code` varchar(2) NOT NULL,
              `name` varchar(180) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // INSERT data string for table: "state"
        $this->execute("
            INSERT INTO `state` VALUES(1, 'AL', 'Alabama');
INSERT INTO `state` VALUES(2, 'AK', 'Alaska');
INSERT INTO `state` VALUES(3, 'AZ', 'Arizona');
INSERT INTO `state` VALUES(4, 'AR', 'Arkansas');
INSERT INTO `state` VALUES(5, 'CA', 'California');
INSERT INTO `state` VALUES(6, 'CO', 'Colorado');
INSERT INTO `state` VALUES(7, 'CT', 'Connecticut');
INSERT INTO `state` VALUES(8, 'DE', 'Delaware');
INSERT INTO `state` VALUES(9, 'DC', 'District of Columbia');
INSERT INTO `state` VALUES(10, 'FL', 'Florida');
INSERT INTO `state` VALUES(11, 'GA', 'Georgia');
INSERT INTO `state` VALUES(12, 'HI', 'Hawaii');
INSERT INTO `state` VALUES(13, 'ID', 'Idaho');
INSERT INTO `state` VALUES(14, 'IL', 'Illinois');
INSERT INTO `state` VALUES(15, 'IN', 'Indiana');
INSERT INTO `state` VALUES(16, 'IA', 'Iowa');
INSERT INTO `state` VALUES(17, 'KS', 'Kansas');
INSERT INTO `state` VALUES(18, 'KY', 'Kentucky');
INSERT INTO `state` VALUES(19, 'LA', 'Louisiana');
INSERT INTO `state` VALUES(20, 'ME', 'Maine');
INSERT INTO `state` VALUES(21, 'MD', 'Maryland');
INSERT INTO `state` VALUES(22, 'MA', 'Massachusetts');
INSERT INTO `state` VALUES(23, 'MI', 'Michigan');
INSERT INTO `state` VALUES(24, 'MN', 'Minnesota');
INSERT INTO `state` VALUES(25, 'MS', 'Mississippi');
INSERT INTO `state` VALUES(26, 'MO', 'Missouri');
INSERT INTO `state` VALUES(27, 'MT', 'Montana');
INSERT INTO `state` VALUES(28, 'NE', 'Nebraska');
INSERT INTO `state` VALUES(29, 'NV', 'Nevada');
INSERT INTO `state` VALUES(30, 'NH', 'New Hampshire');
INSERT INTO `state` VALUES(31, 'NJ', 'New Jersey');
INSERT INTO `state` VALUES(32, 'NM', 'New Mexico');
INSERT INTO `state` VALUES(33, 'NY', 'New York');
INSERT INTO `state` VALUES(34, 'NC', 'North Carolina');
INSERT INTO `state` VALUES(35, 'ND', 'North Dakota');
INSERT INTO `state` VALUES(36, 'OH', 'Ohio');
INSERT INTO `state` VALUES(37, 'OK', 'Oklahoma');
INSERT INTO `state` VALUES(38, 'OR', 'Oregon');
INSERT INTO `state` VALUES(39, 'PA', 'Pennsylvania');
INSERT INTO `state` VALUES(40, 'RI', 'Rhode Island');
INSERT INTO `state` VALUES(41, 'SC', 'South Carolina');
INSERT INTO `state` VALUES(42, 'SD', 'South Dakota');
INSERT INTO `state` VALUES(43, 'TN', 'Tennessee');
INSERT INTO `state` VALUES(44, 'TX', 'Texas');
INSERT INTO `state` VALUES(45, 'UT', 'Utah');
INSERT INTO `state` VALUES(46, 'VT', 'Vermont');
INSERT INTO `state` VALUES(47, 'VA', 'Virginia');
INSERT INTO `state` VALUES(48, 'WA', 'Washington');
INSERT INTO `state` VALUES(49, 'WV', 'West Virginia');
INSERT INTO `state` VALUES(50, 'WI', 'Wisconsin');
INSERT INTO `state` VALUES(51, 'WY', 'Wyoming');
INSERT INTO `state` VALUES(52, 'AB', 'Alberta');
INSERT INTO `state` VALUES(53, 'BC', 'British Columbia');
INSERT INTO `state` VALUES(54, 'MB', 'Manitoba');
INSERT INTO `state` VALUES(55, 'NL', 'Newfoundland');
INSERT INTO `state` VALUES(56, 'NB', 'New Brunswick');
INSERT INTO `state` VALUES(57, 'NS', 'Nova Scotia');
INSERT INTO `state` VALUES(58, 'NT', 'Northwest Territories');
INSERT INTO `state` VALUES(59, 'NU', 'Nunavut');
INSERT INTO `state` VALUES(60, 'ON', 'Ontario');
INSERT INTO `state` VALUES(61, 'PE', 'Prince Edward Island');
INSERT INTO `state` VALUES(62, 'QC', 'Quebec');
INSERT INTO `state` VALUES(63, 'SK', 'Saskatchewan');
INSERT INTO `state` VALUES(64, 'YT', 'Yukon Territory');
        ");

        // CREATE table string for table: "subscriber"
        $this->execute("
            CREATE TABLE `subscriber` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `fname` varchar(180) NOT NULL,
              `lname` varchar(180) NOT NULL,
              `email` varchar(180) NOT NULL,
              `address1` varchar(255) NOT NULL,
              `address2` varchar(11) NOT NULL,
              `city` varchar(25) NOT NULL,
              `state` varchar(5) NOT NULL,
              `postal_code` varchar(11) NOT NULL,
              `country` varchar(5) NOT NULL,
              `code` text NOT NULL,
              `allowed` enum('true','false') NOT NULL DEFAULT 'true',
              `bounces` int(11) NOT NULL,
              `ip` varchar(30) NOT NULL,
              `addedBy` int(11) NOT NULL,
              `addDate` datetime NOT NULL,
              `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `email` (`email`),
              KEY `addedBy` (`addedBy`),
              CONSTRAINT `subscriber_ibfk_1` FOREIGN KEY (`addedBy`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "subscriber_list"
        $this->execute("
            CREATE TABLE `subscriber_list` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `lid` bigint(20) NOT NULL,
              `sid` bigint(20) NOT NULL,
              `addDate` datetime NOT NULL,
              `code` varchar(255) NOT NULL,
              `confirmed` enum('1','0') NOT NULL DEFAULT '0',
              `unsubscribe` enum('1','0') NOT NULL DEFAULT '0',
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `lid` (`lid`),
              KEY `sid` (`sid`),
              CONSTRAINT `subscriber_list_ibfk_1` FOREIGN KEY (`lid`) REFERENCES `list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `subscriber_list_ibfk_2` FOREIGN KEY (`sid`) REFERENCES `subscriber` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "tracking"
        $this->execute("
            CREATE TABLE `tracking` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `cid` bigint(20) NOT NULL,
              `sid` bigint(20) NOT NULL,
              `first_open` datetime DEFAULT NULL,
              `viewed` bigint(20) NOT NULL,
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `cid` (`cid`),
              KEY `sid` (`sid`),
              CONSTRAINT `tracking_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `tracking_ibfk_2` FOREIGN KEY (`sid`) REFERENCES `subscriber` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "user"
        $this->execute("
            CREATE TABLE `user` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `fname` varchar(180) NOT NULL,
              `lname` varchar(180) NOT NULL,
              `uname` varchar(180) NOT NULL,
              `email` varchar(200) NOT NULL,
              `address1` varchar(180) NOT NULL,
              `address2` varchar(25) NOT NULL,
              `city` varchar(80) NOT NULL,
              `state` varchar(2) NOT NULL,
              `postal_code` varchar(10) NOT NULL,
              `country` varchar(4) NOT NULL,
              `password` varchar(200) NOT NULL,
              `code` varchar(255) NOT NULL,
              `status` enum('1','0') NOT NULL DEFAULT '1',
              `roleID` int(11) NOT NULL,
              `date_added` datetime NOT NULL,
              `LastLogin` datetime NOT NULL,
              `LastUpdate` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // INSERT data string for table: "user"
        $this->execute("
            INSERT INTO `user` VALUES(1, 'Tiny', 'Campaign', 'tinyc', 'joshmac3@icloud.com', '', '', 'Boston', 'MA', '02108', 'US', '$2a$08$0S4AFqozF/OXQtzkiR8/U.MJdaQmfUNVjR3fBJ3RqGWCxLkdIp1N2', '', '1', 1, '" . $NOW . "', '2017-02-01 08:27:29', '2017-01-31 03:32:25');
        ");

        // CREATE table string for table: "user_perms"
        $this->execute("
            CREATE TABLE `user_perms` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `userID` int(11) NOT NULL,
              `permission` text NOT NULL,
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `userID` (`userID`) USING BTREE,
              CONSTRAINT `user_perms_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // CREATE table string for table: "user_roles"
        $this->execute("
            CREATE TABLE `user_roles` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userID` int(11) NOT NULL,
              `roleID` bigint(20) NOT NULL,
              `addDate` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `userID` (`userID`,`roleID`) USING BTREE,
              CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ");

        // INSERT data string for table: "user_roles"
        $this->execute("
            INSERT INTO `user_roles` VALUES(1, 1, 1, '" . $NOW . "');
        ");

        // re-arm foreign key checks
        $this->execute('SET FOREIGN_KEY_CHECKS=1;');
        
        if (!file_exists('.htaccess')) {
            copy('htaccess.txt', '.htaccess');
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
