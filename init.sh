#!/bin/bash

composre install

cp ./.env.example ./.env

vendor/bin/phinx init