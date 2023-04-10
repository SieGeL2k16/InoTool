# InoTool - Toolkit of private tools for freelancer management and managing private bank accounts

## What is this?

If you are working as freelancer you have to manage your customer, invoices and other stuff.
Of course there are plenty of tools out there to help you managing your daily business, however I always
prefer to have my own software in place, so here it is: InoTool

## Features

### Freelancer Manager

- Manage your customer
- Manage your projects
- Manage your time sheets
- Extensive reporting
- Supports service contracts
- PDF reporting

### Bank Accounting Manager 

- Supports multiple banking accounts
- Imports Deutsche Bank and Targobank CSV files
- Auto-assignment of repetive accounting data
- Extensive reporting


## Installation

Make sure to meet the requirements:

- PHP 8.1 or newer - lower versions are not supported!
- PostgreSQL 12.x or newer - tested with PgSql 14.5
- Modern browser (see https://getbootstrap.com/docs/5.2/getting-started/browsers-devices/)

If you have already the symfony binary on your system, check your system with:

`symfony check:requirements`

### install copy from gitHub to your work space:

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

#### Create `.env.local` file in Symfony root directory and takeover the following variables:
```
APP_ENV=prod
DATABASE_URL=postgresql://inotool:inotool2k21@127.0.0.1:5432/inotool?serverVersion=14&charset=utf8
```
Please refer to the supplied `.env` file for a description of these variables.

#### Create database structure from Symfony:

`bin/console doctrine:migrations:migrate`

#### Add a user to work with the application:

`bin/console app:user`

####  Use your webbrowser to call the application (public is webdirectory!)


## Frameworks and third-party libraries used

- Symfony 6.x (https://symfony.com/)
- Bootstrap 5.x (https://getbootstrap.com/)
- FontAwesome 6.x (https://fontawesome.com/v6.0/icons)
- jQuery 3.6.x (https://jquery.com/)
- Chart.js (https://www.chartjs.org)
- TCPDF 6.5.0 (http://www.tcpdf.org)
- Luxon (https://moment.github.io/luxon/)
