# BG05SFOP001-3.001-0013_11.12.2017

## Prerequisites

Application server

 - apache 2.4+
 - php 7.2+
 - mySQL 5.6+
 - PHP Composer

Extensions and configuration

 - Apache: mod_rewrite, webroot pointing to "public" subfolder of project, Allowoverride All
 - PHP enable: gd / imagick, zip, SimpleXML, mbstring, iconv, openssl, sockets

## Installation

 - run "composer install" in the root of the project
 - make sure all subfolders in storage are writeable by the PHP / Apache process
 - import storage/database/dump.sql into a new empty database
 - create a ".env" file in the root of the project (look at .env.sample)

