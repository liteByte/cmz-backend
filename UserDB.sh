#!/bin/bash
  
EXPECTED_ARGS=3
E_BADARGS=65
MYSQL=`which mysql`

Q2="FLUSH PRIVILEGES;"  
Q3="CREATE DATABASE IF NOT EXISTS $1;"
Q4="CREATE USER '$2'@'localhost' IDENTIFIED BY '$3';"
Q5="GRANT USAGE ON *.* TO '$2'@'localhost' IDENTIFIED BY '$3';"
Q6="GRANT ALL PRIVILEGES ON $1.* TO '$2'@'localhost';"
Q7="FLUSH PRIVILEGES;"
SQL="${Q2}${Q3}${Q4}${Q5}${Q6}${Q7}"
  
if [ $# -ne $EXPECTED_ARGS ]
then
  echo "Usage: $0 dbname dbuser dbpass"
  exit $E_BADARGS
fi
  
$MYSQL -uroot -p -e "$SQL"
