FROM php:8.2-apache

# Install PostgreSQL extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache to listen on PORT env variable (Railway requirement)
RUN echo "Listen \${PORT:-80}" > /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/' /etc/apache2/sites-available/000-default.conf

# Copy custom Apache startup script (fixes MPM issue)
COPY start-apache.sh /usr/local/bin/start-apache.sh
RUN chmod +x /usr/local/bin/start-apache.sh

# Expose port
EXPOSE 80

# Use custom startup script
CMD ["/usr/local/bin/start-apache.sh"]
