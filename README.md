![FKSDB logo](https://github.com/fykosak/fksdb/blob/web/www/images/logo/blue.svg?raw=true)

![Lines of code](https://img.shields.io/tokei/lines/github/fykosak/fksdb) ![GitHub commit activity](https://img.shields.io/github/commit-activity/y/fykosak/fksdb) \
![GitHub issues](https://img.shields.io/github/issues/fykosak/fksdb) ![GitHub pull requests](https://img.shields.io/github/issues-pr/fykosak/fksdb)  <img src="https://img.shields.io/badge/coverage-43%25-yellow" /> ![GitHub branch checks state](https://img.shields.io/github/checks-status/fykosak/fksdb/web)


Installation
============

  * install PHP74 + extension
    * php7.4
    * php7.4-cli
    * php7.4-common
    * php7.4-curl
    * php7.4-json
    * php7.4-mbstring
    * php7.4-mysql
    * php7.4-opcache
    * php7.4-readline
    * php7.4-soap
    * php7.4-sqlite3
    * php7.4-xdebug
    * php7.4-xml
    * php7.4-xmlrpc
  * install composer
  * install MySQL/mariaDB



Initialization
--------------

0.1) Checkout branch `master`

0.2) Run `git submodule init && git submodule update`

0.3) Run `composer install`

Environment
-----------

1) Create `/etc/hosts` entries for addresses `db.fykos.local` and `auth.fykos.local`.

```
127.0.0.1   fykos.local fykos.localen 
127.0.0.1   db.fykos.local db.fykos.localen
127.0.0.1   auth.fykos.local
```

2) Configure virtual hosts in `/etc/apache/sites-available` with proper ServerName
   and ServerAlias (see domains above). Create a symlink to that file in `/etc/apache/sites-enabled` (You need only virtual host for FKSDB.)

```apache
<VirtualHost db.fykos.local auth.fykos.local>
	ServerAdmin webmaster@localhost
	DocumentRoot /absolute/path/to/fksdb/root/www/

	ErrorLog ${APACHE_LOG_DIR}/error-fksdb.log
        <Directory /absolute/path/to/fksdb/root/www/>
            Options FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>


        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel notice

        CustomLog ${APACHE_LOG_DIR}/access-fksdb.log combined
</VirtualHost>
```

Database
--------

4) Run `sql/schema.sql` and `sql/initval.sql` in your MySQL database.

5) Run `sql/views.sql` in your MySQL database. May require two times execution
   due to bad sorting.

Configuration
-------------

6) Copy `app/config/config.local.neon.sample` to `app/config/config.local.neon`
   and fill it with proper values (don't forget to add domain settings like:
    domain:
        cz: fykos.local
        org: fykos.localen
        tld: local

7) Make directories temp/, log/ and upload/ writable by your Apache user.


Run
===

1) Register yourself and then add superuser role to the created login.

Troubleshooting
---------------
- make sure the `path` has the `+x` property, i.e. `sudo chmod +x /home`, `sudo chmod +x /home/user`, `sudo chmod +x /home/user/fksdb_path`

