# InoTool - Toolkit of private tools for freelancer management, accounting of my bank accounts etc.

## Installation

Make sure to met the requirements:

- PHP 8.0 or newer - lower versions are not supported!
- PostgreSQL 9.x or newer - tested with PgSql 12.9

If you have already the symfony binary on your system, check your system with:

`symfony check:requirements`

### install copy from github to your work space:

1. `git clone https://github.com/SieGeL2k16/InoTool.git`
2. Install composer packages:
   `composer install`

### Setup Database

Create a new database user and database for InoTool, i.e.:

1. `su - postgres`
3. `psql`
2. `postgres=# CREATE ROLE inotool LOGIN CREATEDB PASSWORD 'inotool2k21';`
3. `postgres=# CREATE DATABASE inotool OWNER inotool;`

## Configure Symfony

1. Create `.env.local` file in Symfony root directory and takeover the following variables:
   ```
   APP_ENV=prod
   DATABASE_URL=postgresql://inotool:inotool2k21@127.0.0.1:5432/inotool?serverVersion=12&charset=utf8
   ```
   Please refer to the supplied `.env` file for a description of these variables.


2. Create database structure from Symfony:

   `bin/console doctrine:migrations:migrate`


3. Use your webbrowser to call the application (public is webdirectory!)
