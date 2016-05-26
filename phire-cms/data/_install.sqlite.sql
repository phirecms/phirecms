--
-- Installation profile for a SQLite installation of Phire CMS 2
--
-- Requires the following modules:
--
-- phire-content
-- phire-media
-- phire-categories
-- phire-fields
-- phire-navigation
-- phire-templates
--

INSERT INTO "[{prefix}]content_types" ("id", "name", "content_type", "strict_publishing", "open_authoring", "in_date", "force_ssl", "order") VALUES (5001, 'Pages', 'text/html', 1, 1, 0, 0, 1);

INSERT INTO "[{prefix}]content" ("id", "type_id", "parent_id", "title", "uri", "slug", "status", "template", "roles", "order", "force_ssl", "hierarchy", "publish", "expire", "created", "updated", "created_by", "updated_by") VALUES (6001, 5001, NULL, 'Home Page', '/', '', 1, '9001', 'a:0:{}', 0, 0, '', datetime('now', 'localtime', 'start of day'), NULL, datetime('now', 'localtime', 'start of day'), NULL, 1001, NULL);
INSERT INTO "[{prefix}]content" ("id", "type_id", "parent_id", "title", "uri", "slug", "status", "template", "roles", "order", "force_ssl", "hierarchy", "publish", "expire", "created", "updated", "created_by", "updated_by") VALUES (6002, 5001, NULL, 'About Us', '/about-us', 'about-us', 1, '9001', 'a:0:{}', 0, 0, '', datetime('now', 'localtime', 'start of day'), NULL, datetime('now', 'localtime', 'start of day'), NULL, 1001, NULL);

INSERT INTO "[{prefix}]fields" ("id", "group_id", "storage", "type", "name", "label", "values", "default_values", "attributes", "validators", "encrypt", "order", "required", "prepend", "dynamic", "editor", "models") VALUES (11001, NULL, 'eav', 'text', 'description', 'Description', NULL, NULL, 'size="80" style="width: 99.5%;"', 'a:0:{}', 0, 1, 0, 0, 0, NULL, 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}');
INSERT INTO "[{prefix}]fields" ("id", "group_id", "storage", "type", "name", "label", "values", "default_values", "attributes", "validators", "encrypt", "order", "required", "prepend", "dynamic", "editor", "models") VALUES (11002, NULL, 'eav', 'text', 'keywords', 'Keywords', NULL, NULL, 'size="80" style="width: 99.5%;"', 'a:0:{}', 0, 2, 0, 0, 0, NULL, 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}');
INSERT INTO "[{prefix}]fields" ("id", "group_id", "storage", "type", "name", "label", "values", "default_values", "attributes", "validators", "encrypt", "order", "required", "prepend", "dynamic", "editor", "models") VALUES (11003, NULL, 'eav', 'textarea-history', 'content', 'Content', NULL, NULL, 'rows="20" cols="80" style="display: block; width: 100%;"', 'a:0:{}', 0, 3, 0, 0, 0, 'source', 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}');

INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11001, 6001, 'Phire\Content\Model\Content', '"This is the home page."', 1440511951, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11001, 6002, 'Phire\Content\Model\Content', '"This is the about us page."', 1440511981, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11002, 6001, 'Phire\Content\Model\Content', '"phire cms, home page"', 1440511951, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11002, 6002, 'Phire\Content\Model\Content', '"phire cms, about page"', 1440511981, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11003, 6001, 'Phire\Content\Model\Content', '"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede. Curabitur ac augue id erat mollis commodo. Vestibulum nonummy iaculis risus. Quisque posuere. Curabitur cursus tellus sit amet purus.<\/p>"', 1440511951, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11003, 6002, 'Phire\Content\Model\Content', '"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede. Curabitur ac augue id erat mollis commodo. Vestibulum nonummy iaculis risus. Quisque posuere. Curabitur cursus tellus sit amet purus.<\/p>"', 1440511981, NULL);

INSERT INTO "[{prefix}]media_libraries" ("id", "name", "folder", "allowed_types", "disallowed_types", "max_filesize", "actions", "adapter", "order") VALUES (30001, 'Uploads', 'uploads', 'ai,aif,aiff,avi,bmp,bz2,csv,doc,docx,eps,fla,flv,gif,gz,jpe,jpg,jpeg,log,md,mov,mp2,mp3,mp4,mpg,mpeg,otf,pdf,png,ppt,pptx,psd,rar,svg,swf,tar,tbz,tbz2,tgz,tif,tiff,tsv,ttf,txt,wav,wma,wmv,xls,xlsx,xml,zip', 'css,htm,html,js,json,pgsql,php,php3,php4,php5,sql,sqlite,yaml,yml', 25000000, 'a:4:{s:5:"large";a:3:{s:6:"method";s:6:"resize";s:6:"params";s:3:"800";s:7:"quality";i:80;}s:6:"medium";a:3:{s:6:"method";s:6:"resize";s:6:"params";s:3:"480";s:7:"quality";i:80;}s:5:"small";a:3:{s:6:"method";s:9:"cropThumb";s:6:"params";s:3:"160";s:7:"quality";i:70;}s:5:"thumb";a:3:{s:6:"method";s:9:"cropThumb";s:6:"params";s:2:"80";s:7:"quality";i:70;}}', 'Gd', 1);

INSERT INTO "[{prefix}]templates" ("id", "parent_id", "name", "device", "template", "history", "visible") VALUES (9001, NULL, 'Main Template', 'desktop', '<!DOCTYPE html>
<!-- Header //-->
<html>

<head>

    <title>
        [{title}]
    </title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="author" content="Phire CMS" />
    <meta name="keywords" content="[{keywords}]" />
    <meta name="description" content="[{description}]" />
    <meta name="robots" content="all" />
    <meta name="viewport" content="initial-scale=1.0">

</head>

<body>
<!-- Content //-->
    <div id="content">
        <h1>[{title}]</h1>
[{content}]
    </div>
<!-- Footer //-->
</body>

</html>
', NULL, 1);
INSERT INTO "[{prefix}]templates" ("id", "parent_id", "name", "device", "template", "history", "visible") VALUES (9002, NULL, 'Error', 'desktop', '<!DOCTYPE html>
<!-- Header //-->
<html>

<head>

    <title>
        [{title}]
    </title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="initial-scale=1.0">

</head>

<body>
<!-- Content //-->
    <div id="content">
        <h1 style="color: #f00;">[{title}]</h1>
        <p>Sorry, that page was not found.</p>
    </div>
<!-- Footer //-->
</body>

</html>
', NULL, 0);

UPDATE "[{prefix}]modules" SET "order" = 1 WHERE "name" = 'phire-content';
UPDATE "[{prefix}]modules" SET "order" = 2 WHERE "name" = 'phire-media';
UPDATE "[{prefix}]modules" SET "order" = 3 WHERE "name" = 'phire-templates';
UPDATE "[{prefix}]modules" SET "order" = 4 WHERE "name" = 'phire-navigation';
UPDATE "[{prefix}]modules" SET "order" = 5 WHERE "name" = 'phire-categories';
UPDATE "[{prefix}]modules" SET "order" = 6 WHERE "name" = 'phire-fields';
