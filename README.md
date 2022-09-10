# InoTool - Toolkit of private tools for freelancer management and managing private bank accounts

## What is this?

This a port of two very old projects I've coded for my private usage:

### Accounting Manager (handles my bank account)

Used to gather statistics and general overview of my private banking account.

- Developed in 2007
- MySQL based
- Multi-Bank account support
- No frameworks except JPGraph for graph support
- CSV Importer for DB and TargoBank

### Freelancer Manager (manages my Freelancer projects/customer)

Used to handle my freelancer activity including invoices/customer/project management.

- Developed in 2011 as successor of my even older OraOffice Project from 2001
- Oracle 8i+ based
- Multi-tenant
- Uses jQueryUI as framework
- Smarty for templating
- TCPDF for PDF reporting

Both tools are working with PHP8, however the codebase needs a complete rewrite,
so I've decided to port them both to Symfony using PostgreSQL as database backend.


## Installation

Make sure to met the requirements:

- PHP 8.0 or newer - lower versions are not supported!
- PostgreSQL 9.x or newer - tested with PgSql 12.9
- Modern browser (see https://getbootstrap.com/docs/5.1/getting-started/browsers-devices/)

If you have already the symfony binary on your system, check your system with:

`symfony check:requirements`

### install copy from github to your work space:

1. `git clone https://github.com/SieGeL2k16/InoTool.git`

2. Install composer packages:
   `composer install`

### Setup Database

Create a new database user and database for InoTool, i.e.:

1. `su - postgres`
2. `psql`
3. `postgres=# CREATE ROLE inotool LOGIN CREATEDB PASSWORD 'inotool2k21';`
4. `postgres=# CREATE DATABASE inotool OWNER inotool;`

## Configure Symfony / InoTool

1. Create `.env.local` file in Symfony root directory and takeover the following variables:
   ```
   APP_ENV=prod
   DATABASE_URL=postgresql://inotool:inotool2k21@127.0.0.1:5432/inotool?serverVersion=12&charset=utf8
   ```
   Please refer to the supplied `.env` file for a description of these variables.


2. Create database structure from Symfony:

   `bin/console doctrine:migrations:migrate`


3. Add a user to work with the application:

   `bin/console app:user`


4. Use your webbrowser to call the application (public is webdirectory!)


## Frameworks and third-party libraries used

- Symfony 6.x (https://symfony.com/)
- Bootstrap 5.x (https://getbootstrap.com/)
- FontAwesome 6.x (https://fontawesome.com/v6.0/icons)
- jQuery 3.6.x (https://jquery.com/)
- Chart.js (https://www.chartjs.org)
