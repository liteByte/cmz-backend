#!/usr/bin/env bash

pwd
cd "staging"
git pull origin staging

today=`date '+%Y-%m-%dT%H:%M:%S'`
commit=$(git show --summary --pretty=format:"%h%x09%an%x09%ad%x09%s")
message="$today $commit"
echo ${message} > push.txt
