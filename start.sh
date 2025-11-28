#!/bin/bash

service cron start
# Start Supervisor
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
