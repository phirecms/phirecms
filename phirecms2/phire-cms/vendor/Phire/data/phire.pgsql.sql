--
-- Phire CMS 2.0 PostgreSQL Database
--

-- --------------------------------------------------------

--
-- Table structure for table "config"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]config" (
"setting" varchar(255) NOT NULL,
"value" text NOT NULL,
PRIMARY KEY ("setting")
) ;

--
-- Dumping data for table "config"
--

INSERT INTO "[{prefix}]config" ("setting", "value") VALUES
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
-- Table structure for table "user_types"
--

CREATE SEQUENCE type_id_seq START 2001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_types" (
  "id" integer NOT NULL DEFAULT nextval('type_id_seq'),
  "type" varchar(255) NOT NULL,
  "default_role_id" integer,
  "login" integer,
  "registration" integer,
  "registration_notification" integer,
  "use_captcha" integer,
  "use_csrf" integer,
  "multiple_sessions" integer,
  "mobile_access" integer,
  "email_as_username" integer,
  "email_verification" integer,
  "force_ssl" integer,
  "track_sessions" integer,
  "verification" integer,
  "approval" integer,
  "unsubscribe_login" integer,
  "global_access" integer,
  "allowed_attempts" integer,
  "session_expiration" integer,
  "timeout_warning" integer,
  "password_encryption" integer,
  "reset_password" integer,
  "reset_password_interval" varchar(255),
  "ip_allowed" text,
  "ip_blocked" text,
  "log_emails" text,
  "log_exclude" text,
  "controller" text,
  "sub_controllers" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE type_id_seq OWNED BY "[{prefix}]user_types"."id";
CREATE INDEX "user_type" ON "[{prefix}]user_types" ("type");

--
-- Dumping data for table "user_types"
--

INSERT INTO "[{prefix}]user_types" ("type", "default_role_id", "login", "registration", "registration_notification", "use_captcha", "use_csrf", "multiple_sessions", "mobile_access", "email_as_username", "email_verification", "force_ssl", "track_sessions", "verification", "approval", "unsubscribe_login", "global_access", "allowed_attempts", "session_expiration", "timeout_warning", "password_encryption", "reset_password", "reset_password_interval", "ip_allowed", "ip_blocked", "log_emails", "log_exclude", "controller", "sub_controllers") VALUES
('user', 3001, 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 30, 0, 4, 0, '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table "user_roles"
--

CREATE SEQUENCE role_id_seq START 3001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_roles" (
  "id" integer NOT NULL DEFAULT nextval('role_id_seq'),
  "type_id" integer NOT NULL,
  "name" varchar(255) NOT NULL,
  "permissions" text,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_role_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE role_id_seq OWNED BY "[{prefix}]user_roles"."id";
CREATE INDEX "role_type_id" ON "[{prefix}]user_roles" ("type_id");
CREATE INDEX "role_name" ON "[{prefix}]user_roles" ("name");

--
-- Dumping data for table "user_roles"
--

INSERT INTO "[{prefix}]user_roles" ("type_id", "name") VALUES
(2001, 'Admin');

ALTER TABLE "[{prefix}]user_types" ADD CONSTRAINT "fk_default_role" FOREIGN KEY ("default_role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table "users"
--

CREATE SEQUENCE user_id_seq START 1001;

CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL DEFAULT nextval('user_id_seq'),
  "type_id" integer,
  "role_id" integer,
  "username" varchar(255) NOT NULL,
  "password" varchar(255) NOT NULL,
  "email" varchar(255) NOT NULL,
  "verified" integer,
  "logins" text,
  "failed_attempts" integer,
  "site_ids" text,
  "created" timestamp,
  "updated" timestamp,
  "updated_pwd" timestamp,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_user_type" FOREIGN KEY ("type_id") REFERENCES "[{prefix}]user_types" ("id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE user_id_seq OWNED BY "[{prefix}]users"."id";
CREATE INDEX "user_type_id" ON "[{prefix}]users" ("type_id");
CREATE INDEX "user_role_id" ON "[{prefix}]users" ("role_id");
CREATE INDEX "username" ON "[{prefix}]users" ("username");
CREATE INDEX "user_email" ON "[{prefix}]users" ("email");

--
-- Dumping data for table "users"
--

-- --------------------------------------------------------

--
-- Table structure for table "user_sessions"
--

CREATE SEQUENCE session_id_seq START 4001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_sessions" (
  "id" integer NOT NULL DEFAULT nextval('session_id_seq'),
  "user_id" integer,
  "ip" varchar(255) NOT NULL,
  "ua" varchar(255) NOT NULL,
  "start" timestamp NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_session_user" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE session_id_seq OWNED BY "[{prefix}]user_sessions"."id";
CREATE INDEX "sess_user_id" ON "[{prefix}]user_sessions" ("user_id");

--
-- Dumping data for table "user_sessions"
--

-- --------------------------------------------------------

--
-- Table structure for table "extensions"
--

CREATE SEQUENCE extension_id_seq START 10001;

CREATE TABLE IF NOT EXISTS "[{prefix}]extensions" (
  "id" integer NOT NULL DEFAULT nextval('extension_id_seq'),
  "name" varchar(255) NOT NULL,
  "file" varchar(255) NOT NULL,
  "type" integer NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE extension_id_seq OWNED BY "[{prefix}]extensions"."id";
CREATE INDEX "ext_name" ON "[{prefix}]extensions" ("name");
CREATE INDEX "ext_type" ON "[{prefix}]extensions" ("type");

--
-- Dumping data for table "extensions"
--

-- --------------------------------------------------------

--
-- Table structure for table "field_groups"
--

CREATE SEQUENCE group_id_seq START 12001;

CREATE TABLE IF NOT EXISTS "[{prefix}]field_groups" (
  "id" integer NOT NULL DEFAULT nextval('group_id_seq'),
  "name" varchar(255),
  "order" integer,
  "dynamic" integer,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE group_id_seq OWNED BY "[{prefix}]field_groups"."id";
CREATE INDEX "field_group_name" ON "[{prefix}]field_groups" ("name");
CREATE INDEX "field_group_order" ON "[{prefix}]field_groups" ("order");

-- --------------------------------------------------------

--
-- Table structure for table "fields"
--

CREATE SEQUENCE field_id_seq START 11001;

CREATE TABLE IF NOT EXISTS "[{prefix}]fields" (
  "id" integer NOT NULL DEFAULT nextval('field_id_seq'),
  "group_id" integer,
  "type" varchar(255),
  "name" varchar(255),
  "label" varchar(255),
  "values" varchar(255),
  "default_values" varchar(255),
  "attributes" varchar(255),
  "validators" varchar(255),
  "encryption" integer NOT NULL,
  "order" integer NOT NULL,
  "required" integer NOT NULL,
  "editor" varchar(255),
  "models" text,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_group_id" FOREIGN KEY ("group_id") REFERENCES "[{prefix}]field_groups" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE field_id_seq OWNED BY "[{prefix}]fields"."id";
CREATE INDEX "field_group_id" ON "[{prefix}]fields" ("group_id");
CREATE INDEX "field_field_type" ON "[{prefix}]fields" ("type");
CREATE INDEX "field_field_name" ON "[{prefix}]fields" ("name");

-- --------------------------------------------------------

--
-- Table structure for table "field_values"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]field_values" (
  "field_id" integer NOT NULL,
  "model_id" integer NOT NULL,
  "value" text,
  "timestamp" integer,
  "history" text,
  UNIQUE ("field_id", "model_id"),
  CONSTRAINT "fk_field_id" FOREIGN KEY ("field_id") REFERENCES "[{prefix}]fields" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE INDEX "field_id" ON "[{prefix}]field_values" ("field_id");
CREATE INDEX "model_id" ON "[{prefix}]field_values" ("model_id");

-- --------------------------------------------------------

--
-- Table structure for table "sites"
--

CREATE SEQUENCE site_id_seq START 13001;

CREATE TABLE IF NOT EXISTS "[{prefix}]sites" (
  "id" integer NOT NULL DEFAULT nextval('site_id_seq'),
  "domain" varchar(255) NOT NULL,
  "document_root" varchar(255) NOT NULL,
  "base_path" varchar(255) NOT NULL,
  "title" varchar(255) NOT NULL,
  "force_ssl" integer NOT NULL,
  "live" integer NOT NULL,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE site_id_seq OWNED BY "[{prefix}]sites"."id";
CREATE INDEX "site_domain" ON "[{prefix}]sites" ("domain");
CREATE INDEX "site_title" ON "[{prefix}]sites" ("title");
CREATE INDEX "site_force_ssl" ON "[{prefix}]sites" ("force_ssl");
CREATE INDEX "site_live" ON "[{prefix}]sites" ("live");
