<?php
require_once 'includes/Auth.php';
require_once 'includes/Procurement.php';
require_once 'includes/helpers.php';

$auth = new Auth();
$auth->requireLogin();

$procurementObj = new Procurement();
$user = $auth->getCurrentUser();

$id = $_GET['id'] ?? null;
if (!$id || !($procurement = $procurementObj->getById($id))) {
    setFlashMessage('error', 'Procurement not found');
    redirect('dashboard.php');
}

// Check permissions
if (!canAccessStage($user['role'], 'disbursement')) {
    setFlashMessage('error', 'You do not have permission to access this stage');
    redirect('dashboard.php');
}

// Get existing data
$stageData = $procurementObj->getStageData($id, 'contract_execution');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'procurement_id' => $id,
        'payment_number' => sanitizeInput($_POST['payment_number']),
        'payment_amount' => sanitizeInput($_POST['payment_amount']),
        'payment_date' => sanitizeInput($_POST['payment_date']),
        'payment_method' => sanitizeInput($_POST['payment_method']),
        'invoice_number' => sanitizeInput($_POST['invoice_number']),
        'payment_notes' => sanitizeInput($_POST['payment_notes']),
        'processed_by' => $user['id']
    ];
    
    $procurementObj->saveDisbursement($data);
    $procurementObj->updateStage($id, 'monitoring');
    
    setFlashMessage('success', 'Payment processed successfully!');
    redirect("view-details.php?id=$id");
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disbursement - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-teal-50 via-slate-50 to-cyan-50 min-h-screen">
    <nav class="bg-white shadow-lg border-b border-slate-200 mb-8">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <a href="dashboard.php" class="flex items-center gap-2 text-slate-600 hover:text-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="font-semibold">Back to Dashboard</span>
                </a>
                <span class="text-sm text-slate-600"><?= htmlspecialchars($user['full_name']) ?></span>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 pb-12">
        <?php if ($flash): ?>
        <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500' ?>">
            <p class="<?= $flash['type'] === 'success' ? 'text-green-700' : 'text-red-700' ?> font-medium">
                <?= htmlspecialchars($flash['message']) ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Header Card -->
        <div class="bg-gradient-to-br from-teal-600 to-cyan-600 text-white rounded-3xl shadow-2xl p-8 mb-8">
            <h1 class="heading-font text-4xl font-bold mb-3">Disbursement & Payment</h1>
            <p class="text-teal-100 text-lg mb-4">Process payments to contractor</p>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mt-4">
                <p class="heading-font text-xl font-bold mb-1"><?= htmlspecialchars($procurement['title']) ?></p>
                <?php if ($stageData): ?>
                    <p class="text-teal-100">Contractor: <?= htmlspecialchars($stageData['winning_bidder']) ?> | Amount: ₱ <?= number_format($stageData['contract_amount'], 2) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <form method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Payment Number</label>
                        <input type="text" name="payment_number" required
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 focus:outline-none transition-all"
                               placeholder="e.g., PMT-2024-001">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Payment Amount (₱)</label>
                        <input type="number" name="payment_amount" step="0.01" required
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 focus:outline-none transition-all text-lg font-semibold"
                               placeholder="0.00">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Payment Date</label>
                        <input type="date" name="payment_date" required
                               value="<?= date('Y-m-d') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Payment Method</label>
                        <select name="payment_method" required
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 focus:outline-none transition-all">
                            <option value="">Select Method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="lddap">LDDAP</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Invoice Number</label>
                        <input type="text" name="invoice_number" required
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 focus:outline-none transition-all"
                               placeholder="Invoice reference number">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Payment Notes</label>
                    <textarea name="payment_notes" rows="4" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-teal-500 focus:ring-2 focus:ring-teal-200 focus:outline-none transition-all"
                              placeholder="Additional payment details..."></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t-2 border-slate-100">
                    <a href="view-details.php?id=<?= $id ?>" 
                       class="px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white py-3 px-6 rounded-xl heading-font text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        Process Payment
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>