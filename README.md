# SSE chat server

## Requirements

- php 7.2
- ext-swoole
- composer

## Install

- clone sources
- `composer install`

## Configure

- copy `config/autoload/local.php.dist` to `config/autoload/local.php`
- put your application secret,  `host` and `port` for swoole server, and cors headers if needed

## Run

`composer serve`
