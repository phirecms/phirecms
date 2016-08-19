--
-- Pop Bootstrap MySQL Database
--

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `[{prefix}]roles`;
CREATE TABLE IF NOT EXISTS `[{prefix}]roles` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `name` varchar(255) NOT NULL,
  `permissions` text,
  PRIMARY KEY (`id`),
  INDEX `role_name` (`name`),
  CONSTRAINT `fk_role_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `[{prefix}]roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2001 ;

--
-- Dumping data for table `roles`
--

INSERT INTO `[{prefix}]roles` (`id`, `parent_id`, `name`, `permissions`) VALUES
(2001, NULL, 'Admin', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `[{prefix}]users`;
CREATE TABLE IF NOT EXISTS `[{prefix}]users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `role_id` int(16),
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255),
  `active` int(1),
  `verified` int(1),
  `last_login` datetime,
  `last_ip` varchar(255),
  `last_ua` varchar(255),
  `total_logins` int(16),
  `failed_attempts` int(16),
  PRIMARY KEY (`id`),
  INDEX `role_id` (`role_id`),
  INDEX `username` (`username`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

