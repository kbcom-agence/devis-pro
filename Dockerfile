# Solution Nginx + PHP-FPM (moderne et sans problème MPM)
FROM php:8.2-fpm

# Install Nginx and PostgreSQL extension
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Expose port
EXPOSE 80

# Start Nginx + PHP-FPM
CMD ["/start.sh"]
