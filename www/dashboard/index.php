<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Vérifier l'authentification
requireAuth();

$pdo = getDBConnection();
$userId = getUserId();

// Récupérer les statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM quotes WHERE user_id = ?");
$stmt->execute([$userId]);
$totalQuotes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM quotes WHERE user_id = ? AND status = 'draft'");
$stmt->execute([$userId]);
$draftQuotes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM quotes WHERE user_id = ? AND status = 'sent'");
$stmt->execute([$userId]);
$sentQuotes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM quotes WHERE user_id = ? AND status = 'accepted'");
$stmt->execute([$userId]);
$acceptedQuotes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM quotes WHERE user_id = ? AND status = 'accepted'");
$stmt->execute([$userId]);
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Récupérer les derniers devis
$stmt = $pdo->prepare("
    SELECT id, quote_number, client_name, total_amount, status, quote_date, created_at
    FROM quotes
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentQuotes = $stmt->fetchAll();

$pageTitle = 'Tableau de bord';
include '../includes/header.php';
?>

<div class="card-header">
    <h1>Tableau de bord</h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total devis</h3>
        <div class="value"><?php echo $totalQuotes; ?></div>
    </div>

    <div class="stat-card warning">
        <h3>Brouillons</h3>
        <div class="value"><?php echo $draftQuotes; ?></div>
    </div>

    <div class="stat-card">
        <h3>Envoyés</h3>
        <div class="value"><?php echo $sentQuotes; ?></div>
    </div>

    <div class="stat-card success">
        <h3>Acceptés</h3>
        <div class="value"><?php echo $acceptedQuotes; ?></div>
    </div>

    <div class="stat-card success">
        <h3>Chiffre d'affaires</h3>
        <div class="value"><?php echo formatMoney($totalRevenue); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Devis récents</h2>
    </div>

    <?php if (empty($recentQuotes)): ?>
        <p style="text-align: center; color: var(--text-light); padding: 2rem;">
            Aucun devis pour le moment.
            <br><br>
            <a href="/quotes/create.php" class="btn btn-primary">Créer votre premier devis</a>
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentQuotes as $quote): ?>
                    <tr>
                        <td><?php echo e($quote['quote_number']); ?></td>
                        <td><?php echo e($quote['client_name']); ?></td>
                        <td><?php echo formatDate($quote['quote_date']); ?></td>
                        <td><?php echo formatMoney($quote['total_amount']); ?></td>
                        <td>
                            <span class="status-badge <?php echo getStatusClass($quote['status']); ?>">
                                <?php echo translateStatus($quote['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="/quotes/view.php?id=<?php echo $quote['id']; ?>" class="btn btn-sm btn-secondary">Voir</a>
                                <a href="/quotes/edit.php?id=<?php echo $quote['id']; ?>" class="btn btn-sm btn-primary">Éditer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="/quotes/" class="btn btn-secondary">Voir tous les devis</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
