#/bin/bash

composer install

cp ./.env.example ./.env

vendor/bin/phinx init
