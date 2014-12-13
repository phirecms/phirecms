--
-- Phire CMS 2.0 SQLite Database
--

-- --------------------------------------------------------

--
-- Set database encoding
--

PRAGMA encoding = "UTF-8";
PRAGMA foreign_keys = ON;

-- --------------------------------------------------------

--
-- Table structure for table "config"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]config" (
  "setting" varchar NOT NULL PRIMARY KEY,
  "value" text NOT NULL,
  UNIQUE ("setting")
) ;

--
-- Dumping data for table "config"
--

INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('domain', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('document_root', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('installed_on', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('updated_on', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('datetime_format', 'M j Y g:i A');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('password_encryption', '4');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('pagination_limit', '25');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('pagination_range', '10');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('force_ssl', '0');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('live', '1');

-- --------------------------------------------------------

--
-- Table structure for table "user_roles"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_roles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "permissions" text,
  "login" integer,
  "registration" integer,
  "registration_notification" integer,
  "email_as_username" integer,
  "verification" integer,
  "approval" integer,
  UNIQUE ("id")
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]user_roles', 2000);
CREATE INDEX "user_role_name" ON "[{prefix}]user_roles" ("name");

--
-- Dumping data for table "user_roles"
--

INSERT INTO "[{prefix}]user_roles" ("id", "name", "permissions", "login", "registration", "registration_notification", "email_as_username", "verification", "approval") VALUES
(2001, 'Admin', NULL, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- --
-- Table structure for table "users"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer,
  "username" varchar NOT NULL,
  "password" varchar NOT NULL,
  "email" varchar NOT NULL,
  "verified" integer,
  "created" datetime,
  "updated" datetime,
  UNIQUE ("id"),
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]users', 1000);
CREATE INDEX "user_role_id" ON "[{prefix}]users" ("role_id");
CREATE INDEX "username" ON "[{prefix}]users" ("username");
CREATE INDEX "user_email" ON "[{prefix}]users" ("email");

--
-- Dumping data for table "users"
--

-- --------------------------------------------------------

-- --
-- Table structure for table "modules"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]modules" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "file" varchar NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  UNIQUE ("id")
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]modules', 3000);
CREATE INDEX "module_name" ON "[{prefix}]modules" ("name");

--
-- Dumping data for table "modules"
--

-- --------------------------------------------------------