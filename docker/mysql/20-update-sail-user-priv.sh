#!/usr/bin/env bash

mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_USER'@'%';
    FLUSH PRIVILEGES;
EOSQL