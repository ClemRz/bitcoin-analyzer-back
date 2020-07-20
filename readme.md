# Backend for bitcoin-analyzer project

## Setup

### Dependencies

This application runs with PHP 7.3.

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
