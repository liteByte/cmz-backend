#!/usr/bin/env bash

cd "staging"

# Pull latest changes from the staging branch
git pull origin staging

#echo "start"
#until $(curl --silent --fail curl 208.68.39.205/staging/migrate/down); do
#    printf '.'
#    sleep 5
#done
#echo "done"

# Create push.txt and store last commit pulled data and current date
today=`date '+%Y-%m-%dT%H:%M:%S'`
echo Last push: ${today} > push.txt
TZ=UTC git --no-pager log --pretty=format:"%h%x09%an%x09%ai%x09%s" -n5 >> push.txt
