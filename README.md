# Backend for bitcoin-analyzer project

By [Clément Ronzon](https://www.linkedin.com/in/clemrz/). Licensed under [MIT License](https://choosealicense.com/licenses/mit/).

This is the back-end implementation of a Bitcoin price analyzer.
This price analyzer displays a graph with the price trends of the Bitcoin.

The price analyzer fetches historical Bitcoin prices from a public URL (Yahoo). It stores it into a database (cron job). A REST API serves the data to the front-end.

## Deployment with docker-compose

### Requirements

Docker version `19.03.0+`

### Steps

Clone this repository on your machine:

```shell script
$ git clone https://github.com/ClemRz/bitcoin-analyzer-front.git
$ cd bitcoin-analyzer-front
```

Make sure you rename `src/.env.example` to `src/.env` and fill in the database access information.

When using docker-compose it is important to set the host and the port accordingly:

```
DB_HOST=ba_back_mysql8
DB_PORT=3306
```

The rest of the `.env` variables can be set as you wish, both the PHP application and Docker will take them into account.

Launch the services:

```shell script
$ docker-compose up
```

Test the API: http://localhost:8081/api/1594789200/1594875600/BTCUSD.json

## Deployment without Docker

### tl;dr:

```shell script
$ git clone https://github.com/ClemRz/bitcoin-analyzer-back.git
$ cd bitcoin-analyzer-back
$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$ php composer-setup.php
$ php -r "unlink('composer-setup.php');"
$ php composer.phar install
$ mysql -uroot -p
mysql> CREATE DATABASE `bitcoin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> CREATE USER 'db_user'@'db_host' identified by 'db_password';
mysql> GRANT ALL on bitcoin.* to 'db_user'@'db_host';
mysql> quit
$ mv src/.env.example src/.env
$ vim src/.env
$ mysql -u db_user -p < src/scripts/sql/initialize.sql
$ php src/scripts/run.php initialize
$ crontab -e
$ # add this line: 0 0 * * * php path/to/src/scripts/run.php update
```
### Requirements

This application needs `PHP 7.3` and `MySQL 8.0`.

### Dependencies

Dependencies are managed via [Composer](https://getcomposer.org/) and are listed in `composer.json`.

To install the dependencies run at the root of the project:

```shell script
$ php composer.phar install
```

### Initialization of the database

With the client of your choice execute `src/scripts/sql/initialize.sql`.
This will create the required database, tables and columns.

You will need a MySQL user for this application.
Once you have this information rename `src/.env.example` to `src/.env` and fill in the database access information.

You do not need to set `MYSQL_ROOT_PASSWORD` if you are not using Docker.

Finally run the initialization script that will fetch and store the historical data:

```shell script
$ php src/scripts/run.php initialize
```

### Keep the database up to date

In order for cached values the database to be up to date there is a script that can be executed by a cron job.

Add the following line in the crontab:

```shell script
0 0 * * * php /path/to/src/scripts/run.php update
```

This will run `src/scripts/run.php update` every day at midnight (cron timezone).

Make sure cron has disk access and `run.php` is executable.

## API reference

 - Protocol: `REST`
 - Endpoint: `/`
 - URL format: `/api/{startDate}/{endDate}/{symbol}.{format}`
 - Methods: `GET`
 - Authentication: none
 - Mandatory fields:
   * `startDate`:
     + Description: Unix timestamp (seconds)
     + Type: integer
     + Range: >= `1410825600`
   * `endDate`:
     + Description: Unix timestamp (seconds)
     + Type: integer
     + Range: > `startDate`
   * `symbol`:
     + Description: Representation of the currencies
     + Type: string
     + Available values: `BTC-USD`
   * `format`:
     + Description: desired output format
     + Type: string
     + Available values: `json`
 
 ### Examples
 
Request: `/api/1595030400/1595203199/BTCUSD.json`

Response: 
 ```json
[
    {
        "timestamp": 1595026800,
        "close": "9159.040"
    },
    {
        "timestamp": 1595113200,
        "close": null
    }
]
```
---
Request: `/api/1595203199/1595030400/BTCUSD.json`

Response: 
 ```json
{
    "error": {
        "message": "Validation exception, inconsistency detected: startDate is older than endDate",
        "code": 22
    }
}
```

## Troubleshooting

If you get a `404` or an empty array as a response of the API then maybe something went wrong during the setup.

When using docker-compose, the logs are mapped to the `log` folder at the root of the project (volume). There you will find Apache's and cron's logs.
