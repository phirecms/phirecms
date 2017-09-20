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
  "permissions" text,
  UNIQUE ("id"),
  CONSTRAINT "fk_role_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

CREATE INDEX "role_name" ON "[{prefix}]roles" ("name");

--
-- Dumping data for table "roles"
--

INSERT INTO "[{prefix}]roles" ("id", "parent_id", "name", "permissions") VALUES
(1, NULL, 'Admin', NULL);

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
  "email" varchar,
  "active" integer DEFAULT 0,
  "verified" integer DEFAULT 0,
  "attempts" integer DEFAULT 0,
  UNIQUE ("id"),
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE INDEX "role_id" ON "[{prefix}]users" ("role_id");
CREATE UNIQUE INDEX "username" ON "[{prefix}]users" ("username");
CREATE INDEX "active" ON "[{prefix}]users" ("active");
CREATE INDEX "attempts" ON "[{prefix}]users" ("attempts");


-- --------------------------------------------------------

--
-- Table structure for table "tokens"
--

DROP TABLE IF EXISTS "[{prefix}]tokens";
CREATE TABLE IF NOT EXISTS "[{prefix}]tokens" (
  "user_id" int(16) NOT NULL,
  "token" varchar NOT NULL,
  "refresh" varchar NOT NULL,
  "expires" integer NOT NULL, -- 0, never expires
  "requests" integer DEFAULT 0,
  CONSTRAINT "fk_token_user_id" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE UNIQUE INDEX "access_token" ON "[{prefix}]tokens" ("user_id", "token", "refresh");

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

CREATE INDEX "module_folder" ON "[{prefix}]modules" ("folder");