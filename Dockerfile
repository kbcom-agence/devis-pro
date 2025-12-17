FROM php:8.2-apache

# Install PostgreSQL extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Fix Apache MPM issue - disable event and worker, enable prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

# Configure Apache to listen on PORT env variable (Railway requirement)
RUN echo "Listen \${PORT:-80}" > /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/' /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80
