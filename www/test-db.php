<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Connexion BDD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .result {
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        h1 {
            color: #333;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Test de connexion à PostgreSQL</h1>

    <?php
    $host = getenv('DB_HOST') ?: 'db';
    $port = getenv('DB_PORT') ?: '5432';
    $dbname = getenv('DB_NAME') ?: 'testdb';
    $user = getenv('DB_USER') ?: 'testuser';
    $password = getenv('DB_PASSWORD') ?: 'testpass';

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo '<div class="result success">';
        echo '<h2>✓ Connexion réussie !</h2>';
        echo '<p>Connexion établie avec succès à la base de données PostgreSQL.</p>';
        echo '</div>';

        echo '<h2>Informations de connexion</h2>';
        echo '<table>';
        echo '<tr><th>Paramètre</th><th>Valeur</th></tr>';
        echo "<tr><td>Hôte</td><td>$host</td></tr>";
        echo "<tr><td>Port</td><td>$port</td></tr>";
        echo "<tr><td>Base de données</td><td>$dbname</td></tr>";
        echo "<tr><td>Utilisateur</td><td>$user</td></tr>";
        echo '</table>';

        $version = $pdo->query('SELECT version()')->fetchColumn();
        echo '<h2>Version PostgreSQL</h2>';
        echo '<div class="result success">';
        echo '<p>' . htmlspecialchars($version) . '</p>';
        echo '</div>';

    } catch (PDOException $e) {
        echo '<div class="result error">';
        echo '<h2>✗ Erreur de connexion</h2>';
        echo '<p><strong>Message :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';

        echo '<h2>Paramètres de connexion tentés</h2>';
        echo '<table>';
        echo '<tr><th>Paramètre</th><th>Valeur</th></tr>';
        echo "<tr><td>Hôte</td><td>$host</td></tr>";
        echo "<tr><td>Port</td><td>$port</td></tr>";
        echo "<tr><td>Base de données</td><td>$dbname</td></tr>";
        echo "<tr><td>Utilisateur</td><td>$user</td></tr>";
        echo '</table>';
    }
    ?>

    <a href="index.php">← Retour à l'accueil</a>
</body>
</html>
