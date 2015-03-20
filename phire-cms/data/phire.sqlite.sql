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

DROP TABLE IF EXISTS "[{prefix}]config";
CREATE TABLE IF NOT EXISTS "[{prefix}]config" (
  "setting" varchar NOT NULL PRIMARY KEY,
  "value" text NOT NULL,
  UNIQUE ("setting")
) ;

--
-- Dumping data for table "config"
--

INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('installed_on', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('updated_on', '0000-00-00 00:00:00');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('datetime_format', 'M j Y');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('pagination', '25');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('force_ssl', '0');

-- --------------------------------------------------------

--
-- Table structure for table "roles"
--

DROP TABLE IF EXISTS "[{prefix}]roles";
CREATE TABLE IF NOT EXISTS "[{prefix}]roles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "name" varchar NOT NULL,
  "email_as_username" integer,
  "verification" integer,
  "approval" integer,
  "permissions" text,
  UNIQUE ("id"),
  CONSTRAINT "fk_role_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]roles', 2000);
CREATE INDEX "user_role_name" ON "[{prefix}]roles" ("name");

--
-- Dumping data for table "roles"
--

INSERT INTO "[{prefix}]roles" ("id", "parent_id", "name", "verification", "approval", "email_as_username", "permissions") VALUES
(2001, NULL, 'Phire', 0, 0, 0, NULL);

-- --------------------------------------------------------

-- --
-- Table structure for table "users"
--

DROP TABLE IF EXISTS "[{prefix}]users";
CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer,
  "username" varchar NOT NULL,
  "password" varchar NOT NULL,
  "email" varchar NOT NULL,
  "active" integer,
  "verified" integer,
  UNIQUE ("id"),
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
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

DROP TABLE IF EXISTS "[{prefix}]modules";
CREATE TABLE IF NOT EXISTS "[{prefix}]modules" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "file" varchar NOT NULL,
  "folder" varchar NOT NULL,
  "prefix" varchar NOT NULL,
  "active" integer NOT NULL,
  "assets" text,
  UNIQUE ("id")
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]modules', 3000);
CREATE INDEX "module_folder" ON "[{prefix}]modules" ("folder");
