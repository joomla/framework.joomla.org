--
-- Table setup order:
-- #__packages
-- #__test_results
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

-- --------------------------------------------------------

--
-- Table structure for table `#__test_results`
--

CREATE TABLE IF NOT EXISTS `#__test_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK',
  `package_id` int(11) NOT NULL COMMENT 'Foreign key to #__packages.id',
  `tests` int(11) NOT NULL COMMENT 'Number of tests executed in the package',
  `assertions` int(11) NOT NULL COMMENT 'Number of test assertions in the package',
	`errors` int(11) NOT NULL COMMENT 'Number of test errors in the package',
	`failures` int(11) NOT NULL COMMENT 'Number of test failures in the package',
	`total_lines` int(11) NOT NULL COMMENT 'Lines of code in the package',
	`lines_covered` int(11) NOT NULL COMMENT 'Number of lines of code covered by tests in the package',
  PRIMARY KEY (`id`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `#__test_results_fk_package_id` FOREIGN KEY (`package_id`) REFERENCES `#__packages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
