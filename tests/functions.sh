MYSQL_HOST=${MYSQL_HOST:-127.0.0.1}
MYSQL_USER=${MYSQL_USER:-tester}

function sql() {
	local conf="$(mktemp)"
	cat >"$conf" <<EOD
[client]
host=$MYSQL_HOST
user=$MYSQL_USER
password=$MYSQL_PASSWORD
database=$MYSQL_DATABASE
EOD

	mysql --defaults-extra-file="$conf" "$@"
	rm -f "$conf"
}
