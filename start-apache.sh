#!/bin/bash

# Script de démarrage Apache - Solution ultime pour le problème MPM

echo "🔧 Nettoyage de la configuration Apache MPM..."

# Supprimer TOUS les modules MPM activés
rm -f /etc/apache2/mods-enabled/mpm_*.load
rm -f /etc/apache2/mods-enabled/mpm_*.conf

# Activer UNIQUEMENT mpm_prefork
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

echo "✅ Configuration Apache MPM nettoyée"
echo "🚀 Démarrage d'Apache..."

# Démarrer Apache
exec apache2-foreground
