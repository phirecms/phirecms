--
-- Phire CMS 2.0 MySQL Database
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]config` (
  `setting` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `config`
--

INSERT INTO `[{prefix}]config` (`setting`, `value`) VALUES
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('datetime_format', 'M j Y g:i A'),
('password_encryption', '4'),
('pagination', '25'),
('force_ssl', '0'),
('live', '1');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]roles` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `permissions` text,
  `login` int(1),
  `registration` int(1),
  `registration_notification` int(1),
  `email_as_username` int(1),
  `verification` int(1),
  `approval` int(1),
  PRIMARY KEY (`id`),
  INDEX `user_role_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2002 ;

--
-- Dumping data for table `roles`
--

INSERT INTO `[{prefix}]roles` (`id`, `name`, `permissions`, `login`, `registration`, `registration_notification`, `email_as_username`, `verification`, `approval`) VALUES
(2001, 'Phire', NULL, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `role_id` int(16),
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255) NOT NULL,
  `verified` int(1),
  `created` datetime,
  `updated` datetime,
  PRIMARY KEY (`id`),
  INDEX `user_role_id` (`role_id`),
  INDEX `username` (`username`),
  INDEX `user_email` (`email`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

--
-- Dumping data for table `users`
--

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]modules` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  `assets` text,
  PRIMARY KEY (`id`),
  INDEX `module_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3001 ;

--
-- Dumping data for table `modules`
--

--  --------------------------------------------------------