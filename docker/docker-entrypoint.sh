#!/bin/bash

/usr/local/bin/docker/env.sh

php artisan migrate -n --force
php artisan route:clear

### Start main program
/usr/bin/supervisord