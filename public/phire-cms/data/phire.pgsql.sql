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
('domain', ''),
('document_root', ''),
('installed_on', '0000-00-00 00:00:00'),
('updated_on', '0000-00-00 00:00:00'),
('system_email', ''),
('reply_email', ''),
('default_language', 'en_US'),
('datetime_format', 'M j Y g:i A'),
('pagination_limit', '25'),
('pagination_range', '10'),
('force_ssl', '0'),
('live', '1');

-- --------------------------------------------------------

--
-- Table structure for table "user_roles"
--

CREATE SEQUENCE role_id_seq START 2001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_roles" (
  "id" integer NOT NULL DEFAULT nextval('role_id_seq'),
  "name" varchar(255) NOT NULL,
  "permissions" text,
  "login" integer,
  "registration" integer,
  "registration_notification" integer,
  "email_as_username" integer,
  "verification" integer,
  "approval" integer,
  "password_encryption" varchar(255),
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE role_id_seq OWNED BY "[{prefix}]user_roles"."id";
CREATE INDEX "user_role_name" ON "[{prefix}]user_roles" ("name");

--
-- Dumping data for table "user_roles"
--

INSERT INTO "[{prefix}]user_roles" ("name", "permissions", "login", "registration", "registration_notification", "email_as_username", "verification", "approval", "password_encryption") VALUES
('Admin', NULL, 0, 0, 0, 0, 0, 0, 'Bcrypt');

-- --------------------------------------------------------

--
-- Table structure for table "users"
--

CREATE SEQUENCE user_id_seq START 1001;

CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL DEFAULT nextval('user_id_seq'),
  "role_id" integer,
  "username" varchar(255) NOT NULL,
  "password" varchar(255) NOT NULL,
  "email" varchar(255) NOT NULL,
  "verified" integer,
  "created" timestamp,
  "updated" timestamp,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE user_id_seq OWNED BY "[{prefix}]users"."id";
CREATE INDEX "user_role_id" ON "[{prefix}]users" ("role_id");
CREATE INDEX "username" ON "[{prefix}]users" ("username");
CREATE INDEX "user_email" ON "[{prefix}]users" ("email");

--
-- Dumping data for table "users"
--

-- --------------------------------------------------------

--
-- Table structure for table "modules"
--

CREATE SEQUENCE module_id_seq START 3001;

CREATE TABLE IF NOT EXISTS "[{prefix}]modules" (
  "id" integer NOT NULL DEFAULT nextval('module_id_seq'),
  "name" varchar(255) NOT NULL,
  "file" varchar(255) NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE module_id_seq OWNED BY "[{prefix}]modules"."id";
CREATE INDEX "module_name" ON "[{prefix}]modules" ("name");

--
-- Dumping data for table "modules"
--

-- --------------------------------------------------------