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
('system_version', ''),
('system_domain', ''),
('system_document_root', ''),
('server_operating_system', ''),
('server_software', ''),
('database_version', ''),
('php_version', ''),
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('system_title', 'Phire CMS 2.0'),
('system_email', ''),
('reply_email', ''),
('site_title', 'Default Site Title'),
('separator', '&gt;'),
('default_language', 'en_US'),
('datetime_format', 'M j Y g:i A'),
('media_allowed_types', 'a:27:{i:0;s:2:"ai";i:1;s:3:"bz2";i:2;s:3:"csv";i:3;s:3:"doc";i:4;s:4:"docx";i:5;s:3:"eps";i:6;s:3:"gif";i:7;s:2:"gz";i:8;s:4:"html";i:9;s:3:"htm";i:10;s:3:"jpe";i:11;s:3:"jpg";i:12;s:4:"jpeg";i:13;s:3:"pdf";i:14;s:3:"png";i:15;s:3:"ppt";i:16;s:4:"pptx";i:17;s:3:"psd";i:18;s:3:"svg";i:19;s:3:"swf";i:20;s:3:"tar";i:21;s:3:"txt";i:22;s:3:"xls";i:23;s:4:"xlsx";i:24;s:5:"xhtml";i:25;s:3:"xml";i:26;s:3:"zip";}'),
('media_max_filesize', '25000000'),
('media_actions', 'a:4:{s:5:"large";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:720;s:7:"quality";i:60;}s:6:"medium";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:480;s:7:"quality";i:60;}s:5:"small";a:3:{s:6:"action";s:6:"resize";s:6:"params";i:240;s:7:"quality";i:60;}s:5:"thumb";a:3:{s:6:"action";s:9:"cropThumb";s:6:"params";i:60;s:7:"quality";i:60;}}'),
('media_image_adapter', 'Gd'),
('pagination_limit', '25'),
('pagination_range', '10'),
('force_ssl', '0'),
('live', '1');

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_types` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `default_role_id` int(16),
  `login` int(1),
  `registration` int(1),
  `registration_notification` int(1),
  `use_captcha` int(1),
  `use_csrf` int(1),
  `multiple_sessions` int(1),
  `mobile_access` int(1),
  `email_as_username` int(1),
  `email_verification` int(1),
  `force_ssl` int(1),
  `track_sessions` int(1),
  `verification` int(1),
  `approval` int(1),
  `unsubscribe_login` int(1),
  `global_access` int(1),
  `allowed_attempts` int(16),
  `session_expiration` int(16),
  `timeout_warning` int(1),
  `password_encryption` int(1),
  `reset_password` int(1),
  `reset_password_interval` varchar(255),
  `ip_allowed` text,
  `ip_blocked` text,
  `log_emails` text,
  `log_exclude` text,
  `controller` text,
  `sub_controllers` text,
  PRIMARY KEY (`id`),
  INDEX `user_type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2002 ;

--
-- Dumping data for table `user_types`
--

INSERT INTO `[{prefix}]user_types` (`id`, `type`, `default_role_id`, `login`, `registration`, `registration_notification`, `use_captcha`, `use_csrf`, `multiple_sessions`, `mobile_access`, `email_as_username`, `email_verification`, `force_ssl`, `track_sessions`, `verification`, `approval`, `unsubscribe_login`, `global_access`, `allowed_attempts`, `session_expiration`, `timeout_warning`, `password_encryption`, `reset_password`, `reset_password_interval`, `ip_allowed`, `ip_blocked`, `log_emails`, `log_exclude`, `controller`, `sub_controllers`) VALUES
(2001, 'user', 3001, 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 30, 0, 4, 0, '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_roles` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type_id` int(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `permissions` text,
  PRIMARY KEY (`id`),
  INDEX `role_type_id` (`type_id`),
  INDEX `role_name` (`name`),
  CONSTRAINT `fk_role_type` FOREIGN KEY (`type_id`) REFERENCES `[{prefix}]user_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3002 ;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `[{prefix}]user_roles` (`id`, `type_id`, `name`) VALUES
(3001, 2001, 'Admin');

ALTER TABLE `[{prefix}]user_types` ADD CONSTRAINT `fk_default_role` FOREIGN KEY (`default_role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type_id` int(16),
  `role_id` int(16),
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255) NOT NULL,
  `verified` int(1),
  `logins` text,
  `failed_attempts` int(16),
  `site_ids` text,
  `created` datetime,
  `updated` datetime,
  `updated_pwd` datetime,
  PRIMARY KEY (`id`),
  INDEX `user_type_id` (`type_id`),
  INDEX `user_role_id` (`role_id`),
  INDEX `username` (`username`),
  INDEX `user_email` (`email`),
  CONSTRAINT `fk_user_type` FOREIGN KEY (`type_id`) REFERENCES `[{prefix}]user_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

--
-- Dumping data for table `users`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]user_sessions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `user_id` int(16),
  `ip` varchar(255) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sess_user_id` (`user_id`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `[{prefix}]users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4001 ;

--
-- Dumping data for table `user_sessions`
--

-- --------------------------------------------------------

--
-- Table structure for table `extensions`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]extensions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `type` int(1) NOT NULL,
  `active` int(1) NOT NULL,
  `assets` text,
  PRIMARY KEY (`id`),
  INDEX `ext_name` (`name`),
  INDEX `ext_type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10001 ;

--
-- Dumping data for table `extensions`
--

--  --------------------------------------------------------

--
-- Table structure for table `field_groups`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]field_groups` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `order` int(16),
  `dynamic` int(1),
  PRIMARY KEY (`id`),
  INDEX `field_group_name` (`name`),
  INDEX `field_group_order` (`order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12001 ;

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]fields` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `group_id` int(16),
  `type` varchar(255),
  `name` varchar(255),
  `label` varchar(255),
  `values` text,
  `default_values` text,
  `attributes` varchar(255),
  `validators` varchar(255),
  `encryption` int(1) NOT NULL,
  `order` int(16) NOT NULL,
  `required` int(1) NOT NULL,
  `editor` varchar(255),
  `models` text,
  PRIMARY KEY (`id`),
  INDEX `field_group_id` (`group_id`),
  INDEX `field_type` (`type`),
  INDEX `field_name` (`name`),
  CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `[{prefix}]field_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11001 ;

-- --------------------------------------------------------

--
-- Table structure for table `field_values`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]field_values` (
  `field_id` int(16) NOT NULL,
  `model_id` int(16) NOT NULL,
  `value` mediumtext,
  `timestamp` int(16),
  `history` mediumtext,
  INDEX `field_id` (`field_id`),
  INDEX `model_id` (`model_id`),
  UNIQUE (`field_id`, `model_id`),
  CONSTRAINT `fk_field_id` FOREIGN KEY (`field_id`) REFERENCES `[{prefix}]fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]sites` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `document_root` varchar(255) NOT NULL,
  `base_path` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `force_ssl` int(1) NOT NULL,
  `live` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `site_domain` (`domain`),
  INDEX `site_title` (`title`),
  INDEX `site_force_ssl` (`force_ssl`),
  INDEX `site_live` (`live`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13001 ;
