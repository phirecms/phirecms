Pop Bootstrap
=============

Release Information
-------------------
Version 1.0.3  
August 5, 2016

Overview
--------

A skeleton web application for the Pop Web Application Framework,
using the Bootstrap and Font Awesome frameworks. 

Requirements
------------

* Minimum of PHP 5.4.0 and PHP 7.0 is supported as well
* Apache 2+, IIS 7+, or any web server with URL rewrite support
* Supported Databases:
    - MySQL 5.0+
    - PostgreSQL 9.0+
    - SQLite 3+

Installation
------------

The command below will install all of the necessary components and
take you through the installation steps automatically:

```console
$ composer create-project popphp/pop-bootstrap project-folder
```

Get Started
-----------

Either create a vhost on your web server or start the PHP web server
and point the document root to the `public` folder:

```console
$ sudo php -S localhost:8000 -t public
```

Visit the main web address. If you are using the PHP web server like
above, you would visit `http://localhost:8000`. You will be redirected
to a login screen. The default credentials are:

* Username: `admin`
* Password: `password`

Features
--------

This skeleton application provides a basic set of features common to
web applications. This includes:

- User Login
- User Roles
- User Sessions
- User Management

Console Access
--------------

The application comes with a simple console interface to assist
with user management from the CLI as well. The following commands
are available:

```console
$ ./app help                Show this help screen
    
$ ./app users               List users
$ ./app users add           Add a user
$ ./app users password      Change a user password
$ ./app users activate      Activate a user
$ ./app users deactivate    Deactivate a user
$ ./app users remove        Remove a user
    
$ ./app roles               List roles
$ ./app roles add           Add a role
$ ./app roles edit          Edit a role
$ ./app roles remove        Remove a role
    
$ ./app sessions            List sessions
$ ./app sessions remove     Remove a session
```
