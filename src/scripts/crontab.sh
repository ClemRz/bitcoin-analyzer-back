#!/usr/bin/env bash

# Credit: https://github.com/senorihl/docker-compose-cronjob

# Creates log file
touch /var/log/crontab.log

# Runs the update of the DB every day at midnight
echo "0 0 * * * /usr/local/bin/php /var/www/html/scripts/run.php update >> /var/log/crontab.log 2>&1" > /etc/crontab

# Registers the new crontab
crontab /etc/crontab

# Starts the cron
/usr/sbin/service cron start

# Displays logs
tail -f /var/log/crontab.log