<?php
require_once 'config/session.php';
require_once 'includes/functions.php';

// Si l'utilisateur est connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    redirect('/dashboard/');
}

$pageTitle = 'Accueil';
include 'includes/header.php';
?>

<style>
    .hero {
        text-align: center;
        padding: 4rem 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1rem;
        margin-bottom: 3rem;
    }

    .hero h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .hero p {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .hero .actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .hero .btn {
        background: white;
        color: var(--primary);
        font-size: 1.1rem;
        padding: 1rem 2rem;
    }

    .hero .btn:hover {
        background: var(--bg);
    }

    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .feature {
        background: var(--bg-white);
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: var(--shadow);
        text-align: center;
    }

    .feature h3 {
        color: var(--primary);
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .feature p {
        color: var(--text-light);
        line-height: 1.8;
    }

    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2rem;
        }

        .hero p {
            font-size: 1rem;
        }
    }
</style>

<div class="hero">
    <h1>Devis Pro</h1>
    <p>Gérez vos devis simplement et efficacement</p>
    <div class="actions">
        <a href="/auth/register.php" class="btn">Créer un compte gratuit</a>
        <a href="/auth/login.php" class="btn btn-secondary">Se connecter</a>
    </div>
</div>

<div class="features">
    <div class="feature">
        <h3>🚀 Simple et rapide</h3>
        <p>Créez vos devis en quelques clics avec une interface intuitive et moderne.</p>
    </div>

    <div class="feature">
        <h3>📊 Suivi complet</h3>
        <p>Suivez l'état de vos devis : brouillon, envoyé, accepté ou refusé.</p>
    </div>

    <div class="feature">
        <h3>💰 Statistiques</h3>
        <p>Visualisez votre chiffre d'affaires et vos performances en temps réel.</p>
    </div>

    <div class="feature">
        <h3>🔒 Sécurisé</h3>
        <p>Vos données sont protégées avec un système d'authentification sécurisé.</p>
    </div>

    <div class="feature">
        <h3>📱 Responsive</h3>
        <p>Accédez à vos devis depuis n'importe quel appareil, mobile ou desktop.</p>
    </div>

    <div class="feature">
        <h3>✉️ Notifications</h3>
        <p>Recevez des emails de confirmation et de récupération de mot de passe.</p>
    </div>
</div>

<div class="card" style="text-align: center;">
    <h2 style="margin-bottom: 1rem;">Prêt à commencer ?</h2>
    <p style="color: var(--text-light); margin-bottom: 2rem;">
        Créez votre compte gratuitement et commencez à gérer vos devis professionnellement.
    </p>
    <a href="/auth/register.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">
        Créer un compte maintenant
    </a>
</div>

<div style="text-align: center; margin-top: 2rem; padding: 1rem; background: var(--bg); border-radius: 0.5rem;">
    <p style="color: var(--text-light); font-size: 0.9rem;">
        <strong>Note:</strong> Pour initialiser la base de données, visitez
        <a href="/init-db.php" style="color: var(--primary);">init-db.php</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
