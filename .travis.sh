#!/bin/bash

# This script is called in the "before_script" section of .travis.yml.
# Its purpose is to install the platform according to the build configuration.

cp app/config/local/parameters.yml.dist app/config/local/parameters.yml

if [$DB = mysql] then 
    sed -i 's/root/travis/' app/config/local/parameters.yml
elif [$DB = pgsql] then 
    sed -i 's/root/postgres/; s/pdo_mysql/pdo_pgsql' app/config/local/parameters.yml
fi

composer --prefer-source --dev install
php app/console doctrine:database:create --env=test
php app/console claroline:install --env=test
