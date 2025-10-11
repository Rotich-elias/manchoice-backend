#!/bin/bash
# Wrapper script to run Laravel artisan commands with correct database config

# Unset DB_CONNECTION to let .env take precedence
unset DB_CONNECTION

# Run artisan with all passed arguments
php artisan "$@"
