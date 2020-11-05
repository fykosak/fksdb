Installation
============

Initialization
--------------

0.1) Checkout branch 'dev'

0.2) Run `git submodule init && git submodule update`

Environment
-----------

1) Create /etc/hosts entries for addresses 'db.fykos.local' and 'auth.fykos.local'.

127.0.0.1   fykos.local fykos.localen 
127.0.0.1   db.fykos.local db.fykos.localen
127.0.0.1   auth.fykos.local

2) Configure virtual hosts in '/etc/apache/sites-enabled' with proper ServerName
   and ServerAlias (see domains above). (You need only virtual host for FKSDB.)

3) Install missing PHP extensions (like php5-mysql, php5-curl).

Database
--------

4) Run 'sql/schema.sql' and 'sql/initval.sql' in your MySQL database.

5) Run 'sql/views.sql' in your MySQL database. May require two times execution
   due to bad sorting.

Configuration
-------------

6) Copy 'app/config/config.local.neon.sample' to 'app/config/config.local.neon'
   and fill it with proper values (don't forget to add domain settings like:
    domain:
        cz: fykos.local
        org: fykos.localen
        tld: local

7) Make directories temp/, log/ and upload/ writable by your Apache user.


Run
===

1) Register yourself and then add superuser role to the created login.

<img src="https://img.shields.io/badge/coverage-39%25-yellow" />
