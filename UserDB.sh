#!/bin/bash

EXPECTED_ARGS=2
E_BADARGS=65
MYSQL=`which mysql`

Q1="DROP DATABASE IF EXISTS cmz;"
Q2="FLUSH PRIVILEGES;"
Q3="CREATE DATABASE IF NOT EXISTS cmz;"
Q4="CREATE USER '$1'@'localhost' IDENTIFIED BY '$2';"
Q5="GRANT USAGE ON *.* TO '$1'@'localhost' IDENTIFIED BY '$2';"
Q6="GRANT ALL PRIVILEGES ON cmz.* TO '$1'@'localhost';"
Q7="FLUSH PRIVILEGES;"
SQL="${Q1}${Q2}${Q3}${Q4}${Q5}${Q6}${Q7}"

if [ $# -ne $EXPECTED_ARGS ]
then
  echo "Usage: $0 dbuser dbpass"
  exit $E_BADARGS
fi

$MYSQL -uroot -p -e "$SQL"
