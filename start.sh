#!/bin/bash

echo "🚀 Démarrage de PHP-FPM..."
php-fpm -D

echo "🚀 Démarrage de Nginx..."
nginx -g 'daemon off;'
