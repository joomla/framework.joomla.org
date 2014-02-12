--
-- Table setup order:
-- #__packages
--

--
-- Table structure for table `#__packages`
--

CREATE TABLE IF NOT EXISTS `#__packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `package` varchar(50) NOT NULL COMMENT 'Package Name',
  `version` varchar(25) NOT NULL COMMENT 'Package version',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
