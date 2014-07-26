Phire CMS 2.0.0b Installation
=============================

Quick Install
-------------

Once you have your database set up with the proper credentials, to begin the installation process,  
you can simply go to the URL: http://www.yourdomain.com/phirecms2/phire/install

Of course, you can move it to another folder or just have it at the top level as well:  
http://www.yourdomain.com/phire/install

The installation is a simple 3 step process:

1. Database credentials and system info
2. The code to be pasted into the config file (this step is skipped if the config file is writable.)
3. Initial user set up

And that's it! You can start using Phire CMS 2. The login link will be:  
http://www.yourdomain.com/phirecms2/phire/login


Advanced Options
----------------

You can tweak how you install Phire CMS 2 in a couple of ways. Obviously, on the 1st step installation
screen, you can set what prefix is used for the database tables and what the content path will be, if you
don't want them to be the standard 'ph_' and '/phire-content', respectively. Of course, if you want the
content path to be something different, you'll have to rename that folder, and make sure it remains
writable.

The 'Application URI' option gives you control over how you access Phire CMS 2. If you don't want the
access to be the standard '/phire' URI, then you can simply change it here to something else, like
'/system', or whatever you want it to be.

Further more, once the system is installed, if you'd like to change the location of the system files,
you can do that as well. By default, the system files are in the folder '/phire-cms'. You can rename
that folder and then change the value of the 'APP_PATH' constant in the config file to match that
new system folder name.
