--
-- Phire CMS SQLite Database
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

INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('installed', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('updated', '');
INSERT INTO "[{prefix}]config" ("setting", "value") VALUES ('updates', '');

-- --------------------------------------------------------

--
-- Table structure for table "roles"
--

DROP TABLE IF EXISTS "[{prefix}]roles";
CREATE TABLE IF NOT EXISTS "[{prefix}]roles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer,
  "name" varchar NOT NULL,
  "verification" integer,
  "approval" integer,
  "permissions" text,
  UNIQUE ("id"),
  CONSTRAINT "fk_role_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('roles', 2000);
CREATE INDEX "role_name" ON "[{prefix}]roles" ("name");

--
-- Dumping data for table "roles"
--

INSERT INTO "[{prefix}]roles" ("id", "parent_id", "name", "verification", "approval", "permissions") VALUES
(2001, NULL, 'Admin', 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table "users"
--

DROP TABLE IF EXISTS "[{prefix}]users";
CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer,
  "username" varchar NOT NULL,
  "password" varchar NOT NULL,
  "email" varchar(255),
  "active" integer,
  "verified" integer,
  UNIQUE ("id"),
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('users', 1000);
CREATE INDEX "role_id" ON "[{prefix}]users" ("role_id");
CREATE INDEX "username" ON "[{prefix}]users" ("username");


-- --------------------------------------------------------

-- --
-- Table structure for table "modules"
--

DROP TABLE IF EXISTS "[{prefix}]modules";
CREATE TABLE IF NOT EXISTS "[{prefix}]modules" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "file" varchar NOT NULL,
  "folder" varchar NOT NULL,
  "name" varchar NOT NULL,
  "prefix" varchar NOT NULL,
  "version" varchar NOT NULL,
  "description" text DEFAULT NULL,
  "author" varchar DEFAULT NULL,
  "active" integer NOT NULL,
  "order" integer NOT NULL,
  "assets" text DEFAULT NULL,
  "installed" datetime DEFAULT NULL,
  "updated" datetime DEFAULT NULL,
  "updates" text DEFAULT NULL,
  UNIQUE ("id")
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]modules', 3000);
CREATE INDEX "module_folder" ON "[{prefix}]modules" ("folder");