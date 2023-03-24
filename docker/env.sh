#!/bin/bash


### Generate .env file
laravel_env_vars=(
    DB_CONNECTION
    DB_HOST
    DB_PORT
    DB_DATABASE
    DB_USERNAME
    DB_PASSWORD
)
for env_var in "${laravel_env_vars[@]}"; do
    file_env_var="${env_var}_FILE"
    if [[ -n "${!file_env_var:-}" ]]; then
        if [[ -r "${!file_env_var:-}" ]]; then
            export "${env_var}=$(< "${!file_env_var}")"
            unset "${file_env_var}"
        else
            warn "Skipping export of '${env_var}'. '${!file_env_var:-}' is not readable."
        fi
    fi
done
unset laravel_env_vars