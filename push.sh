#!/usr/bin/env bash

# Colors
PURPLE='\033[0;35m'
GREEN='\033[0;32m'
NC='\033[0m'

# Pull latest changes from the staging branch
echo "\n${PURPLE}Pulling repo...${NC}\n"
git pull origin staging

# Compose
echo "\n${PURPLE}Composing app...${NC}\n"
php /bin/composer.phar install

# Run migrations
echo "\n${PURPLE}Migrating database...${NC}\n"
php index.php Migrate down
php index.php Migrate up

# Create push.txt and store last commit pulled data and current date
echo "\n${PURPLE}Writing log...${NC}\n"
today=`date '+%Y-%m-%dT%H:%M:%S'`
echo Last push: ${today} > push.txt
TZ=UTC git --no-pager log --pretty=format:"%h%x09%an%x09%ai%x09%s" -n5 >> push.txt

echo "\n${GREEN}Done!${NC}\n"