<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Vérifier l'authentification
requireAuth();

$pdo = getDBConnection();
$userId = getUserId();
$quoteId = (int) ($_GET['id'] ?? 0);

// Récupérer le devis
$stmt = $pdo->prepare("
    SELECT * FROM quotes
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$quoteId, $userId]);
$quote = $stmt->fetch();

if (!$quote) {
    setFlashMessage('error', 'Devis introuvable.');
    redirect('/quotes/');
}

// Récupérer les lignes
$stmt = $pdo->prepare("
    SELECT * FROM quote_items
    WHERE quote_id = ?
    ORDER BY position ASC
");
$stmt->execute([$quoteId]);
$items = $stmt->fetchAll();

// Mise à jour du statut si demandé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $newStatus = $_POST['new_status'];
        $stmt = $pdo->prepare("UPDATE quotes SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $quoteId]);

        setFlashMessage('success', 'Statut mis à jour avec succès.');
        redirect("/quotes/view.php?id={$quoteId}");
    }
}

$pageTitle = 'Détail du devis';
include '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h1>Devis <?php echo e($quote['quote_number']); ?></h1>
    <div class="actions">
        <a href="/quotes/edit.php?id=<?php echo $quote['id']; ?>" class="btn btn-primary">Éditer</a>
        <a href="/quotes/" class="btn btn-secondary">Retour à la liste</a>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem;">
        <div>
            <h2 style="color: var(--primary); margin-bottom: 0.5rem;">Devis Pro</h2>
            <p style="color: var(--text-light);">Application de gestion de devis</p>
        </div>
        <div style="text-align: right;">
            <h3><?php echo e($quote['quote_number']); ?></h3>
            <p style="color: var(--text-light);">
                Date: <?php echo formatDate($quote['quote_date']); ?><br>
                Validité: <?php echo $quote['validity_days']; ?> jours
            </p>
        </div>
    </div>

    <hr style="margin: 2rem 0; border: none; border-top: 2px solid var(--border);">

    <div class="form-row" style="margin-bottom: 2rem;">
        <div>
            <h3 style="margin-bottom: 1rem;">Client</h3>
            <p>
                <strong><?php echo e($quote['client_name']); ?></strong><br>
                <?php if ($quote['client_address']): ?>
                    <?php echo e($quote['client_address']); ?><br>
                <?php endif; ?>
                <?php if ($quote['client_email']): ?>
                    Email: <?php echo e($quote['client_email']); ?><br>
                <?php endif; ?>
                <?php if ($quote['client_phone']): ?>
                    Tél: <?php echo e($quote['client_phone']); ?>
                <?php endif; ?>
            </p>
        </div>
        <div style="text-align: right;">
            <h3 style="margin-bottom: 1rem;">Statut</h3>
            <span class="status-badge <?php echo getStatusClass($quote['status']); ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                <?php echo translateStatus($quote['status']); ?>
            </span>

            <form method="POST" style="margin-top: 1rem;">
                <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                <select name="new_status" class="btn btn-sm" onchange="this.form.submit()">
                    <option value="">Changer le statut...</option>
                    <option value="draft">Brouillon</option>
                    <option value="sent">Envoyé</option>
                    <option value="accepted">Accepté</option>
                    <option value="rejected">Refusé</option>
                </select>
            </form>
        </div>
    </div>

    <h3 style="margin-bottom: 1rem;">Articles / Services</h3>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Quantité</th>
                <th style="text-align: right;">Prix unitaire</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo e($item['description']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['quantity'], 2, ',', ' '); ?></td>
                    <td style="text-align: right;"><?php echo formatMoney($item['unit_price']); ?></td>
                    <td style="text-align: right;"><strong><?php echo formatMoney($item['total']); ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: 700; font-size: 1.2rem; padding-top: 1rem;">Total HT:</td>
                <td style="text-align: right; font-weight: 700; font-size: 1.2rem; color: var(--primary); padding-top: 1rem;">
                    <?php echo formatMoney($quote['total_amount']); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <?php if ($quote['payment_terms'] || $quote['notes']): ?>
        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <?php if ($quote['payment_terms']): ?>
            <div style="margin-bottom: 1.5rem;">
                <h4>Conditions de paiement</h4>
                <p><?php echo nl2br(e($quote['payment_terms'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($quote['notes']): ?>
            <div>
                <h4>Notes</h4>
                <p><?php echo nl2br(e($quote['notes'])); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div style="margin-top: 1.5rem; padding: 1rem; background: var(--bg); border-radius: 0.5rem; color: var(--text-light); font-size: 0.875rem;">
    <p>Créé le: <?php echo formatDate($quote['created_at']); ?></p>
    <p>Dernière modification: <?php echo formatDate($quote['updated_at']); ?></p>
</div>

<?php include '../includes/footer.php'; ?>
