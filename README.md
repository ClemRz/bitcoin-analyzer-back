# Backend for bitcoin-analyzer project

By [Clément Ronzon](https://www.linkedin.com/in/clemrz/).

Licensed under [MIT License](https://choosealicense.com/licenses/mit/)

## Deployment

### tl;dr:

```shell script
$ git clone https://github.com/ClemRz/bitcoin-analyzer-back.git .
$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$ php -r "if (hash_file('sha384', 'composer-setup.php') === 'e5325b19b381bfd88ce90a5ddb7823406b2a38cff6bb704b0acc289a09c8128d4a8ce2bbafcd1fcbdc38666422fe2806') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
$ php composer-setup.php
$ php -r "unlink('composer-setup.php');"
$ php composer.phar install
$ mysql -uroot -p
mysql> CREATE DATABASE `bitcoin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> CREATE USER 'db_user'@'db_host' identified by 'db_password';
mysql> GRANT ALL on bitcoin.* to 'db_user'@'db_host';
mysql> quit
$ mv .env.example .env
$ vim .env
$ mysql -udb_user -p < scripts/initialize.sql
$ php scripts/initialize.php
$ crontab -e
$ # add this line: 0 6 * * * php path/to/scripts/update.php
```

### Dependencies

This application needs `PHP 7.3` and `MySQL 8.0`.

Dependencies are managed via [Composer](https://getcomposer.org/) and are listed in `composer.json`.

To install the dependencies run at the root of the project:

```shell script
$ php composer.phar install
```

### Initialization of the database

With the client of your choice execute `scripts/initialize.sql`.
This will create the require database (`bitcoin`), table and columns.

You will need a MySQL user for this application.
Once you have this information rename `.env.example` to `.env` and fill in the database access information.

Finally run the initialization script that will fetch and store the historical data:

```shell script
$ php scripts/initialize.php
```

### Keep the database up to date

In order for cached values the database to be up to date there is a script that can be executed by a cron job.

Add the following line in the crontab:

```shell script
0 6 * * * php /path/to/scripts/update.php
```

This will run `scripts/update.php` every day at 6am (cron timezone).

Make sure cron has disk access and `update.php` is executable.

## API documentation

 - Protocol: `REST`
 - Endpoint: `/`
 - Format: `/{symbol}/{startDate}/{endDate}.{format}`
 - Methods: `GET`
 - Authentication: none
 - Required fields:
   * `symbol`:
     + Description: Representation of the currencies
     + Type: string
     + Available values: `BTC-USD`
   * `startDate`:
     + Description: Unix timestamp (seconds)
     + Type: integer
     + Range: >= `1410825600`
   * `endDate`:
     + Description: Unix timestamp (seconds)
     + Type: integer
     + Range: > `startDate`
   * `format`:
     + Description: desired output format
     + Type: string
     + Available values: `json`
 
 ### Examples
Request: `/BTCUSD/1595030400/1595203199.json`

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
Request: `/BTCUSD/1595203199/1595030400.json`

Response: 
 ```json
{
    "error": {
        "message": "Validation exception, inconsistency detected: startDate is older than endDate",
        "code": 22
    }
}
```
