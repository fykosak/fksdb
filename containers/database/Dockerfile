FROM mariadb:10.4

COPY containers/database/mysql.cnf   /etc/mysql/conf.d/10-mysql.cnf
COPY sql/*                           /docker-entrypoint-initdb.d
