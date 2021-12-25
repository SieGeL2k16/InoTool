# InoTool - Toolkit of private tools for freelancer management, accounting of my bank accounts etc.

## Installation

Install copy from gitlab to your work space:

1. `git clone ssh://git@gitlab.inolares.de:60022/inocore/haccp.git`
2. Install composer packages:
   `composer install`

### Database

Create first a new database user and database for HACCP, i.e.:

1. `su - postgres`
3. `psql`
2. `postgres=# CREATE ROLE inotool LOGIN CREATEDB PASSWORD 'inotool2k21';`
3. `postgres=# CREATE DATABASE inotool OWNER inotool;`

### Configure Symfony

1. Create `.env.local` file in Symfony root directory and takeover the following variables:
   ```
   DATABASE_URL=postgresql://inotool:inotool2k21@127.0.0.1:5432/inotool?serverVersion=12&charset=utf8
   ```
   Please refer to the supplied `.env` file for a description of these variables.


2. Create database structure from Symfony:

   `bin/console doctrine:migrations:migrate`


3. Use your webbrowser to call the application (public is webdirectory!)
