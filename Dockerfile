FROM php:8.2-apache

# Disable multiple MPM modules (keep only mpm_prefork for mod_php)
RUN a2dismod mpm_event mpm_worker

# Install PostgreSQL extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*
