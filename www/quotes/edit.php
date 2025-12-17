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
$stmt = $pdo->prepare("SELECT * FROM quotes WHERE id = ? AND user_id = ?");
$stmt->execute([$quoteId, $userId]);
$quote = $stmt->fetch();

if (!$quote) {
    setFlashMessage('error', 'Devis introuvable.');
    redirect('/quotes/');
}

// Récupérer les lignes
$stmt = $pdo->prepare("SELECT * FROM quote_items WHERE quote_id = ? ORDER BY position ASC");
$stmt->execute([$quoteId]);
$existingItems = $stmt->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Erreur de sécurité.";
    } else {
        $clientName = sanitize($_POST['client_name'] ?? '');
        $clientEmail = sanitize($_POST['client_email'] ?? '');
        $clientPhone = sanitize($_POST['client_phone'] ?? '');
        $clientAddress = sanitize($_POST['client_address'] ?? '');
        $quoteDate = $_POST['quote_date'] ?? date('Y-m-d');
        $validityDays = (int) ($_POST['validity_days'] ?? 30);
        $paymentTerms = sanitize($_POST['payment_terms'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        $itemDescriptions = $_POST['item_description'] ?? [];
        $itemQuantities = $_POST['item_quantity'] ?? [];
        $itemUnitPrices = $_POST['item_unit_price'] ?? [];

        if (empty($clientName)) {
            $errors[] = "Le nom du client est requis.";
        }

        if (!empty($clientEmail) && !isValidEmail($clientEmail)) {
            $errors[] = "L'adresse email est invalide.";
        }

        if (empty($itemDescriptions)) {
            $errors[] = "Ajoutez au moins une ligne.";
        }

        $totalAmount = 0;
        $items = [];

        foreach ($itemDescriptions as $index => $description) {
            if (empty($description)) continue;

            $quantity = (float) ($itemQuantities[$index] ?? 0);
            $unitPrice = (float) ($itemUnitPrices[$index] ?? 0);
            $lineTotal = $quantity * $unitPrice;

            $items[] = [
                'description' => sanitize($description),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $lineTotal,
                'position' => $index
            ];

            $totalAmount += $lineTotal;
        }

        if (empty($items)) {
            $errors[] = "Ajoutez au moins une ligne valide.";
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Mettre à jour le devis
                $stmt = $pdo->prepare("
                    UPDATE quotes SET
                        client_name = ?, client_email = ?, client_phone = ?, client_address = ?,
                        quote_date = ?, total_amount = ?, status = ?, validity_days = ?,
                        payment_terms = ?, notes = ?, updated_at = NOW()
                    WHERE id = ?
                ");

                $stmt->execute([
                    $clientName, $clientEmail, $clientPhone, $clientAddress,
                    $quoteDate, $totalAmount, $status, $validityDays,
                    $paymentTerms, $notes, $quoteId
                ]);

                // Supprimer les anciennes lignes
                $pdo->prepare("DELETE FROM quote_items WHERE quote_id = ?")->execute([$quoteId]);

                // Insérer les nouvelles lignes
                $stmt = $pdo->prepare("
                    INSERT INTO quote_items (quote_id, description, quantity, unit_price, total, position)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                foreach ($items as $item) {
                    $stmt->execute([
                        $quoteId,
                        $item['description'],
                        $item['quantity'],
                        $item['unit_price'],
                        $item['total'],
                        $item['position']
                    ]);
                }

                $pdo->commit();

                setFlashMessage('success', "Devis {$quote['quote_number']} mis à jour avec succès !");
                redirect("/quotes/view.php?id={$quoteId}");

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Une erreur est survenue.";
                error_log("Erreur update devis: " . $e->getMessage());
            }
        }
    }
} else {
    // Pré-remplir avec les données existantes
    $_POST = [
        'client_name' => $quote['client_name'],
        'client_email' => $quote['client_email'],
        'client_phone' => $quote['client_phone'],
        'client_address' => $quote['client_address'],
        'quote_date' => $quote['quote_date'],
        'validity_days' => $quote['validity_days'],
        'payment_terms' => $quote['payment_terms'],
        'notes' => $quote['notes'],
        'status' => $quote['status']
    ];
}

$pageTitle = 'Modifier le devis';
include '../includes/header.php';
?>

<div class="card-header">
    <h1>Modifier le devis <?php echo e($quote['quote_number']); ?></h1>
</div>

<div class="card">
    <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="quoteForm">
        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

        <h3>Informations client</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="client_name">Nom du client *</label>
                <input type="text" id="client_name" name="client_name" required value="<?php echo e($_POST['client_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="client_email">Email</label>
                <input type="email" id="client_email" name="client_email" value="<?php echo e($_POST['client_email'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="client_phone">Téléphone</label>
                <input type="tel" id="client_phone" name="client_phone" value="<?php echo e($_POST['client_phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="client_address">Adresse</label>
                <input type="text" id="client_address" name="client_address" value="<?php echo e($_POST['client_address'] ?? ''); ?>">
            </div>
        </div>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <h3>Informations du devis</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="quote_date">Date du devis</label>
                <input type="date" id="quote_date" name="quote_date" value="<?php echo $_POST['quote_date'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="validity_days">Validité (jours)</label>
                <input type="number" id="validity_days" name="validity_days" value="<?php echo $_POST['validity_days'] ?? 30; ?>" min="1">
            </div>

            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo ($_POST['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                    <option value="sent" <?php echo ($_POST['status'] ?? '') === 'sent' ? 'selected' : ''; ?>>Envoyé</option>
                    <option value="accepted" <?php echo ($_POST['status'] ?? '') === 'accepted' ? 'selected' : ''; ?>>Accepté</option>
                    <option value="rejected" <?php echo ($_POST['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Refusé</option>
                </select>
            </div>
        </div>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <h3>Lignes du devis</h3>
        <div id="quoteItems">
            <?php foreach ($existingItems as $index => $item): ?>
                <div class="quote-item">
                    <div class="form-group">
                        <label>Description *</label>
                        <input type="text" name="item_description[]" required value="<?php echo e($item['description']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Quantité *</label>
                        <input type="number" name="item_quantity[]" required step="0.01" min="0.01" value="<?php echo $item['quantity']; ?>" class="item-quantity">
                    </div>
                    <div class="form-group">
                        <label>Prix unitaire *</label>
                        <input type="number" name="item_unit_price[]" required step="0.01" min="0" value="<?php echo $item['unit_price']; ?>" class="item-price">
                    </div>
                    <div class="form-group">
                        <label>Total</label>
                        <input type="text" class="item-total" readonly value="<?php echo formatMoney($item['total']); ?>">
                    </div>
                    <div>
                        <label>&nbsp;</label>
                        <button type="button" class="btn-remove" onclick="removeItem(this)" <?php echo $index === 0 ? 'style="display: none;"' : ''; ?>>✕</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-secondary btn-add-item" onclick="addItem()">+ Ajouter une ligne</button>

        <div class="quote-total">
            <h3>Total HT: <span id="grandTotal"><?php echo formatMoney($quote['total_amount']); ?></span></h3>
        </div>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <h3>Conditions et notes</h3>
        <div class="form-group">
            <label for="payment_terms">Conditions de paiement</label>
            <textarea id="payment_terms" name="payment_terms" rows="3"><?php echo e($_POST['payment_terms'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="notes">Notes additionnelles</label>
            <textarea id="notes" name="notes" rows="3"><?php echo e($_POST['notes'] ?? ''); ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="/quotes/view.php?id=<?php echo $quoteId; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script src="/assets/js/quote-form.js"></script>

<?php include '../includes/footer.php'; ?>
