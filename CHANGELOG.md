Phire CMS 2.0.0b Changelog
==========================

Phire CMS 2 is a complete rewrite of the application from the ground up. So therefore,
it completely breaks any backwards compatibility and really has no architectural
relationship to the 1.* branch. What has been deprecated is listed below.

But here's the good news! Here is a list of what's been added or drastically improved:

2.0.0b
------
Released TBD

#### New Core System Features

* Improved media settings, with the ability to set custom media processing and sizes, while
  preserving the original media file.
* Support for both the GD and Imagick extensions, including vector processing via
  Ghostscript (if installed and properly configured with Imagick.)
* Better support for custom date/time formatting.
* __User types__, which support a more robust and dynamic user base and what users of a certain
  type are allowed to do (login, register, etc.)
* A new, full-featured __ACL sub-system__, including __user roles__, which allows for the setting of system
  permissions, allowing or denying access of certain areas to a user of a certain role.
* A new, completely re-written module sub-system, allowing easier development, installation and
  management of custom-written modules.
* A re-written theme sub-system, allowing easier development, installation and management of
  custom-written themes, which includes:
    - Support for file-based templates for greater control within the PHP environment (*.phtml, *.php, etc.)
    - Support for string-based templates that are managed within the system (like in the 1.* branch.)
* A completely new feature set called __Structure__, which allows for dynamic fields and field groups to be
  assigned to to just about any object within the system (users, content, etc.)
    - Support for the popular CKEditor and TinyMCE editors for textarea field, including multiple
      instances if needed.
    - Support for history/revision recall for textarea and text fields.
* New CLI tool for assistance in managing the system from the command line.

### IMPORTANT
All of the actual content functionality has been stripped out and re-written into a stand-alone Content module.

#### New Content Module Features

* __Content Types__ - allows for any content type to be added, either one that is URI-based or one
  that is file-based.
* __Navigation__ - allows for URI-based content to be added to navigation objects. Includes support for
  the ACL sub-system.
* __Categories__ - a re-written and much improved way of categorizing and sub-categorizing content
  (replaces Sections.)
* Improved file management and uploading capabilities, including better batch and archive uploads.
* Leverages the new fields functionality to allow any content type to have a dynamic set of fields
  associated with it.


Deprecated Features in the 2.* Branch
-------------------------------------

Most of what has been deprecated has either been replaced by something better, or stripped out
to be written into a module.

* Sections
* Feeds
* Comments
* Assets
    - Files
    - Images
    - Events
    - Tags
* Members
* Page Stats
* Site Stats

