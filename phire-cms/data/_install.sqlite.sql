--
-- Installation profile for a SQLite installation of Phire CMS 2
--
-- Requires the following modules and template set:
--
-- phire-categories
-- phire-content
-- phire-fields
-- phire-media
-- phire-navigation
-- phire-templates
--
-- jumbotron-template
--

INSERT INTO "[{prefix}]content_types" ("id", "name", "content_type", "strict_publishing", "open_authoring", "in_date", "force_ssl", "order") VALUES (5001, 'Pages', 'text/html', 1, 1, 0, 0, 1);

INSERT INTO "[{prefix}]content" ("id", "type_id", "parent_id", "title", "uri", "slug", "status", "template", "roles", "order", "force_ssl", "hierarchy", "publish", "expire", "created", "updated", "created_by", "updated_by") VALUES (6001, 5001, NULL, 'Home', '/', '', 1, '9001', 'a:0:{}', 0, 0, '', datetime('now', 'localtime', 'start of day'), NULL, datetime('now', 'localtime', 'start of day'), NULL, 1001, 1001);
INSERT INTO "[{prefix}]content" ("id", "type_id", "parent_id", "title", "uri", "slug", "status", "template", "roles", "order", "force_ssl", "hierarchy", "publish", "expire", "created", "updated", "created_by", "updated_by") VALUES (6002, 5001, NULL, 'About', '/about', 'about', 1, '9002', 'a:0:{}', 0, 0, '', datetime('now', 'localtime', 'start of day'), NULL, datetime('now', 'localtime', 'start of day'), NULL, 1001, 1001);
INSERT INTO "[{prefix}]content" ("id", "type_id", "parent_id", "title", "uri", "slug", "status", "template", "roles", "order", "force_ssl", "hierarchy", "publish", "expire", "created", "updated", "created_by", "updated_by") VALUES (6003, 5001, NULL, 'Contact', '/contact', 'contact', 1, '9002', 'a:0:{}', 0, 0, '', datetime('now', 'localtime', 'start of day'), NULL, datetime('now', 'localtime', 'start of day'), NULL, 1001, 1001);
INSERT INTO "[{prefix}]content" ("id", "type_id", "parent_id", "title", "uri", "slug", "status", "template", "roles", "order", "force_ssl", "hierarchy", "publish", "expire", "created", "updated", "created_by", "updated_by") VALUES (6004, 5001, 6002, 'The Team', '/about/the-team', 'the-team', 1, '9002', 'a:0:{}', 0, 0, '6002', datetime('now', 'localtime', 'start of day'), NULL, datetime('now', 'localtime', 'start of day'), NULL, 1001, 1001);

INSERT INTO "[{prefix}]fields" ("id", "group_id", "storage", "type", "name", "label", "values", "default_values", "attributes", "validators", "encrypt", "order", "required", "prepend", "dynamic", "editor", "models") VALUES (11001, NULL, 'eav', 'text', 'description', 'Description', NULL, NULL, 'size="80" style="width: 99.5%;"', 'a:0:{}', 0, 1, 0, 0, 0, NULL, 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}');
INSERT INTO "[{prefix}]fields" ("id", "group_id", "storage", "type", "name", "label", "values", "default_values", "attributes", "validators", "encrypt", "order", "required", "prepend", "dynamic", "editor", "models") VALUES (11002, NULL, 'eav', 'text', 'keywords', 'Keywords', NULL, NULL, 'size="80" style="width: 99.5%;"', 'a:0:{}', 0, 2, 0, 0, 0, NULL, 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}');
INSERT INTO "[{prefix}]fields" ("id", "group_id", "storage", "type", "name", "label", "values", "default_values", "attributes", "validators", "encrypt", "order", "required", "prepend", "dynamic", "editor", "models") VALUES (11003, NULL, 'eav', 'textarea-history', 'content', 'Content', NULL, NULL, 'rows="20" cols="80" style="display: block; width: 100%;"', 'a:0:{}', 0, 3, 0, 0, 0, 'source', 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}');

INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11001, 6001, 'Phire\Content\Model\Content', '"This is the home page"', 1464490934, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11001, 6002, 'Phire\Content\Model\Content', '"This is the about us page."', 1464490949, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11001, 6003, 'Phire\Content\Model\Content', '"This is the contact page."', 1464490976, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11001, 6004, 'Phire\Content\Model\Content', '"This is the team page."', 1464490963, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11002, 6001, 'Phire\Content\Model\Content', '"home page, phire cms"', 1464490934, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11002, 6002, 'Phire\Content\Model\Content', '"about us page, phire cms"', 1464490949, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11002, 6003, 'Phire\Content\Model\Content', '"contact page, phire cms"', 1464490976, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11002, 6004, 'Phire\Content\Model\Content', '"team page, phire cms"', 1464490963, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11003, 6001, 'Phire\Content\Model\Content', '"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede.<\/p>"', 1464490934, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11003, 6002, 'Phire\Content\Model\Content', '"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede. Curabitur ac augue id erat mollis commodo. Vestibulum nonummy iaculis risus. Quisque posuere. Curabitur cursus tellus sit amet purus.<\/p><p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat. Nulla facilisi. In blandit auctor justo. Etiam sem nisi, mattis et, consequat non, suscipit quis, arcu. Quisque a diam. Etiam lorem arcu, gravida in, aliquet non, dapibus sed, sapien. Sed ut felis a justo condimentum tincidunt. Aliquam a magna. Mauris est.<\/p>"', 1464490949, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11003, 6003, 'Phire\Content\Model\Content', '"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede. Curabitur ac augue id erat mollis commodo. Vestibulum nonummy iaculis risus. Quisque posuere. Curabitur cursus tellus sit amet purus.<\/p><p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat. Nulla facilisi. In blandit auctor justo. Etiam sem nisi, mattis et, consequat non, suscipit quis, arcu. Quisque a diam. Etiam lorem arcu, gravida in, aliquet non, dapibus sed, sapien. Sed ut felis a justo condimentum tincidunt. Aliquam a magna. Mauris est.<\/p>"', 1464490976, NULL);
INSERT INTO "[{prefix}]field_values" ("field_id", "model_id", "model", "value", "timestamp", "history") VALUES (11003, 6004, 'Phire\Content\Model\Content', '"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin volutpat. Maecenas laoreet tempus quam. Maecenas faucibus semper leo. Nullam sit amet felis. Integer luctus interdum lacus. Vestibulum pulvinar, nunc a fermentum eleifend, dui ipsum condimentum urna, at hendrerit lacus mi elementum tortor. Maecenas lacus. Nunc varius. Duis malesuada. Vivamus facilisis quam et diam. Curabitur augue. Phasellus eros. Aliquam ultrices nisi lobortis pede. Curabitur ac augue id erat mollis commodo. Vestibulum nonummy iaculis risus. Quisque posuere. Curabitur cursus tellus sit amet purus.<\/p><p>Aliquam velit massa, ultricies sit amet, facilisis vitae, placerat vitae, justo. Pellentesque tortor orci, ornare a, consequat ut, mollis et, nisl. Suspendisse sem metus, convallis nec, fermentum sed, varius at, metus. Pellentesque ullamcorper diam eget urna. Aliquam risus risus, imperdiet sit amet, elementum nec, pellentesque vel, justo. Quisque dictum sagittis dolor. Nam nulla. Duis id ipsum. Proin ultrices. Maecenas egestas malesuada erat. Nulla facilisi. In blandit auctor justo. Etiam sem nisi, mattis et, consequat non, suscipit quis, arcu. Quisque a diam. Etiam lorem arcu, gravida in, aliquet non, dapibus sed, sapien. Sed ut felis a justo condimentum tincidunt. Aliquam a magna. Mauris est.<\/p>"', 1464490963, NULL);

INSERT INTO "[{prefix}]media_libraries" ("id", "name", "folder", "allowed_types", "disallowed_types", "max_filesize", "actions", "adapter", "order") VALUES (30001, 'Uploads', 'uploads', 'ai,aif,aiff,avi,bmp,bz2,csv,doc,docx,eps,fla,flv,gif,gz,jpe,jpg,jpeg,log,md,mov,mp2,mp3,mp4,mpg,mpeg,otf,pdf,png,ppt,pptx,psd,rar,svg,swf,tar,tbz,tbz2,tgz,tif,tiff,tsv,ttf,txt,wav,wma,wmv,xls,xlsx,xml,zip', 'css,htm,html,js,json,pgsql,php,php3,php4,php5,sql,sqlite,yaml,yml', 25000000, 'a:4:{s:5:"large";a:3:{s:6:"method";s:6:"resize";s:6:"params";s:3:"800";s:7:"quality";i:80;}s:6:"medium";a:3:{s:6:"method";s:6:"resize";s:6:"params";s:3:"480";s:7:"quality";i:80;}s:5:"small";a:3:{s:6:"method";s:9:"cropThumb";s:6:"params";s:3:"160";s:7:"quality";i:70;}s:5:"thumb";a:3:{s:6:"method";s:9:"cropThumb";s:6:"params";s:2:"80";s:7:"quality";i:70;}}', 'Gd', 1);

INSERT INTO "[{prefix}]templates" ("id", "parent_id", "name", "device", "template", "history", "visible") VALUES (9001, NULL, 'index', 'desktop', '[{template_header}]

    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
      <div class="container">
        <h1>Hello, world!</h1>
        <p>This is a template for a simple marketing or informational website. It includes a large callout called a jumbotron and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
        <p><a class="btn btn-success btn-lg" href="#" role="button">Learn more &raquo;</a></p>
      </div>
    </div>

    <div class="container marketing">
      <h1>[{title}]</h1>
      <div class="padding-bottom">
[{content}]
      </div>

      <!-- Three columns of text below the carousel -->

[{template_company}]

    </div>
      <!-- START THE FEATURETTES -->

    <div class="container">
      <hr class="featurette-divider">

      <div class="row featurette">
        <div class="col-md-7">
          <h2 class="featurette-heading">First featurette heading. <span class="text-muted">It&#39;s awesome.</span></h2>
          <p class="lead">Donec ullamcorper nulla non metus auctor fringilla. Vestibulum id ligula porta felis euismod semper. Praesent commodo cursus magna, vel scelerisque nisl consectetur. Fusce dapibus, tellus ac cursus commodo.</p>
        </div>
        <div class="col-md-5">
          <img class="featurette-image img-responsive center-block" src="[{base_path}][{content_path}]/templates/jumbotron-template-1.0/img/placeholder-sm.gif" alt="Generic placeholder image">
        </div>
      </div>

      <hr class="featurette-divider">

      <div class="row featurette">
        <div class="col-md-7 col-md-push-5">
          <h2 class="featurette-heading">Oh yeah, it&#39;s that good. <span class="text-muted">See for yourself.</span></h2>
          <p class="lead">Donec ullamcorper nulla non metus auctor fringilla. Vestibulum id ligula porta felis euismod semper. Praesent commodo cursus magna, vel scelerisque nisl consectetur. Fusce dapibus, tellus ac cursus commodo.</p>
        </div>
        <div class="col-md-5 col-md-pull-7">
          <img class="featurette-image img-responsive center-block" src="[{base_path}][{content_path}]/templates/jumbotron-template-1.0/img/placeholder-sm.gif" alt="Generic placeholder image">
        </div>
      </div>
    </div> <!-- /container -->

[{template_footer}]', NULL, 1);
INSERT INTO "[{prefix}]templates" ("id", "parent_id", "name", "device", "template", "history", "visible") VALUES (9002, NULL, 'sub', 'desktop', '[{template_header}]

    <div class="container padding-top subheader"></div>

    <div class="container padding-top">
      <h2>[{title}]</h2>
[{content}]

      <hr class="featurette-divider">
    </div>

    <div class="container marketing">

[{template_company}]

    </div> <!-- /container -->

[{template_footer}]', NULL, 1);
INSERT INTO "[{prefix}]templates" ("id", "parent_id", "name", "device", "template", "history", "visible") VALUES (9003, NULL, 'company', 'desktop', '      <!-- Three columns of text below the carousel -->
      <div class="row">
        <div class="col-lg-4">
          <img class="img-circle" src="[{base_path}][{content_path}]/templates/jumbotron-template-1.0/img/man1-240.jpg" alt="Generic placeholder image" width="140" height="140">
          <h2>Heading</h2>
          <p>Donec sed odio dui. Etiam porta sem malesuada magna mollis euismod. Nullam id dolor id nibh ultricies vehicula ut id elit. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Praesent commodo cursus magna.</p>
          <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div><!-- /.col-lg-4 -->
        <div class="col-lg-4">
          <img class="img-circle" src="[{base_path}][{content_path}]/templates/jumbotron-template-1.0/img/woman1-240.jpg" alt="Generic placeholder image" width="140" height="140">
          <h2>Heading</h2>
          <p>Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh.</p>
          <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div><!-- /.col-lg-4 -->
        <div class="col-lg-4">
          <img class="img-circle" src="[{base_path}][{content_path}]/templates/jumbotron-template-1.0/img/man2-240.jpg" alt="Generic placeholder image" width="140" height="140">
          <h2>Heading</h2>
          <p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
          <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div><!-- /.col-lg-4 -->
      </div><!-- /.row -->
', NULL, 0);
INSERT INTO "[{prefix}]templates" ("id", "parent_id", "name", "device", "template", "history", "visible") VALUES (9004, NULL, 'header', 'desktop', '<!DOCTYPE html>
<!-- Header //-->
<html>

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>[{title}] : My Website</title>

    <meta name="keywords" content="[{keywords}]" />
    <meta name="description" content="[{description}]" />

    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300,300italic,400italic,700,700italic,900,900italic" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="[{base_path}][{content_path}]/templates/jumbotron-template-1.0/css/styles.css" />

</head>

<body>

    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="[{base_path}]/">My Logo</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
[{main_nav}]
        </div><!--/.nav-collapse -->
      </div>
    </nav>
', NULL, 0);
INSERT INTO "[{prefix}]templates" ("id", "parent_id", "name", "device", "template", "history", "visible") VALUES (9005, NULL, 'footer', 'desktop', '    <footer class="footer">
      <div class="container">
        <p>&copy; [{date_Y}] Company, Inc.</p>
      </div>
    </footer>
    <script src="https://code.jquery.com/jquery-2.2.3.min.js" integrity="sha256-a23g1Nt4dtEYOj7bR+vTu7+T8VP13humZFBJNIYoEJo=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</body>

</html>', NULL, 0);

INSERT INTO "[{prefix}]navigation" ("id", "title", "top_node", "top_id", "top_class", "top_attributes", "parent_node", "parent_id", "parent_class", "parent_attributes", "child_node", "child_id", "child_class", "child_attributes", "on_class", "off_class", "indent") VALUES (7001, 'Main Nav', 'ul', '', 'nav navbar-nav navbar-right', '', 'ul', '', '', '', 'li', '', '', '', '', '', '0');

INSERT INTO "[{prefix}]navigation_items" ("id", "navigation_id", "parent_id", "item_id", "type", "name", "href", "attributes", "order") VALUES (8001, 7001, NULL, 6001, 'content', 'Home', '/', NULL, 0);
INSERT INTO "[{prefix}]navigation_items" ("id", "navigation_id", "parent_id", "item_id", "type", "name", "href", "attributes", "order") VALUES (8002, 7001, NULL, 6002, 'content', 'About', '/about', NULL, 0);
INSERT INTO "[{prefix}]navigation_items" ("id", "navigation_id", "parent_id", "item_id", "type", "name", "href", "attributes", "order") VALUES (8003, 7001, NULL, 6003, 'content', 'Contact', '/contact', 'a:0:{}', 0);
INSERT INTO "[{prefix}]navigation_items" ("id", "navigation_id", "parent_id", "item_id", "type", "name", "href", "attributes", "order") VALUES (8004, 7001, 8002, 6004, 'content', 'The Team', '/about/the-team', 'a:0:{}', 0);

UPDATE "[{prefix}]modules" SET "order" = 1 WHERE "name" = 'phire-content';
UPDATE "[{prefix}]modules" SET "order" = 2 WHERE "name" = 'phire-media';
UPDATE "[{prefix}]modules" SET "order" = 3 WHERE "name" = 'phire-templates';
UPDATE "[{prefix}]modules" SET "order" = 4 WHERE "name" = 'phire-navigation';
UPDATE "[{prefix}]modules" SET "order" = 5 WHERE "name" = 'phire-categories';
UPDATE "[{prefix}]modules" SET "order" = 6 WHERE "name" = 'phire-fields';
