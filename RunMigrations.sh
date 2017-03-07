#!/bin/bash

if [ -z "$1" ]
then
   curl http://208.68.39.205/cmz/migrate/down
   curl http://208.68.39.205/cmz/migrate/up
else
   if [ $1 == "up" ]
   then
     curl http://208.68.39.205/cmz/migrate/up
   elif [ $1 == "down" ]
   then
     curl http://208.68.39.205/cmz/migrate/down
   else
     echo "Incorrect method"
   fi
fi

echo "Exiting..."
