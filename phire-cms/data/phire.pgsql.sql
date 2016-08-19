--
-- Pop Bootstrap PostgreSQL Database
--

-- --------------------------------------------------------

--
-- Table structure for table "roles"
--

CREATE SEQUENCE role_id_seq START 2001;

DROP TABLE IF EXISTS "[{prefix}]roles" CASCADE;
CREATE TABLE IF NOT EXISTS "[{prefix}]roles" (
  "id" integer NOT NULL DEFAULT nextval('role_id_seq'),
  "parent_id" integer,
  "name" varchar(255) NOT NULL,
  "verification" integer,
  "approval" integer,
  "permissions" text,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_role_parent_id" FOREIGN KEY ("parent_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE SET NULL ON UPDATE CASCADE
) ;

ALTER SEQUENCE role_id_seq OWNED BY "[{prefix}]roles"."id";
CREATE INDEX "role_name" ON "[{prefix}]roles" ("name");

--
-- Dumping data for table "roles"
--

INSERT INTO "[{prefix}]roles" ("parent_id", "name", "verification", "approval", "permissions") VALUES
(NULL, 'Admin', 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table "users"
--

CREATE SEQUENCE user_id_seq START 1001;

DROP TABLE IF EXISTS "[{prefix}]users" CASCADE;
CREATE TABLE IF NOT EXISTS "[{prefix}]users" (
  "id" integer NOT NULL DEFAULT nextval('user_id_seq'),
  "role_id" integer,
  "username" varchar(255) NOT NULL,
  "password" varchar(255) NOT NULL,
  "email" varchar(255),
  "active" integer,
  "verified" integer,
  "last_login" timestamp,
  "last_ip" varchar(255),
  "last_ua" varchar(255),
  "total_logins" integer,
  "failed_attempts" integer,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_user_role" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE user_id_seq OWNED BY "[{prefix}]users"."id";
CREATE INDEX "role_id" ON "[{prefix}]users" ("role_id");
CREATE INDEX "username" ON "[{prefix}]users" ("username");
