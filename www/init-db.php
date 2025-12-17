<?php
/**
 * Script d'initialisation de la base de données
 * À exécuter une seule fois pour créer les tables
 */

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'testdb';
$user = getenv('DB_USER') ?: 'testuser';
$password = getenv('DB_PASSWORD') ?: 'testpass';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Initialisation de la base de données</h1>";
    echo "<pre>";

    // Table users
    echo "Création de la table 'users'...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            is_verified BOOLEAN DEFAULT FALSE,
            verification_token VARCHAR(64) NULL,
            reset_token VARCHAR(64) NULL,
            reset_token_expiry TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT NOW()
        )
    ");
    echo "✓ Table 'users' créée\n\n";

    // Table quotes
    echo "Création de la table 'quotes'...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quotes (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            quote_number VARCHAR(50) UNIQUE NOT NULL,
            client_name VARCHAR(255) NOT NULL,
            client_email VARCHAR(255),
            client_phone VARCHAR(50),
            client_address TEXT,
            quote_date DATE NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'draft',
            validity_days INTEGER DEFAULT 30,
            payment_terms TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT NOW(),
            updated_at TIMESTAMP DEFAULT NOW()
        )
    ");
    echo "✓ Table 'quotes' créée\n\n";

    // Table quote_items
    echo "Création de la table 'quote_items'...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quote_items (
            id SERIAL PRIMARY KEY,
            quote_id INTEGER NOT NULL REFERENCES quotes(id) ON DELETE CASCADE,
            description TEXT NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            position INTEGER NOT NULL
        )
    ");
    echo "✓ Table 'quote_items' créée\n\n";

    // Créer des index pour améliorer les performances
    echo "Création des index...\n";
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_quotes_user_id ON quotes(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_quotes_status ON quotes(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_quote_items_quote_id ON quote_items(quote_id)");
    echo "✓ Index créés\n\n";

    echo "</pre>";
    echo "<h2 style='color: green;'>✓ Base de données initialisée avec succès !</h2>";
    echo "<p><a href='auth/register.php'>Créer un compte</a> | <a href='auth/login.php'>Se connecter</a></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>✗ Erreur lors de l'initialisation</h2>";
    echo "<pre style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit(1);
}
