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
if (!canAccessStage($user['role'], 'contract_execution')) {
    setFlashMessage('error', 'You do not have permission to access this stage');
    redirect('dashboard.php');
}

// Get existing data
$stageData = $procurementObj->getStageData($id, 'contract_execution');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'procurement_id' => $id,
        'winning_bidder' => sanitizeInput($_POST['winning_bidder']),
        'contract_amount' => sanitizeInput($_POST['contract_amount']),
        'contract_date' => sanitizeInput($_POST['contract_date']),
        'contract_duration' => sanitizeInput($_POST['contract_duration']),
        'deliverables' => sanitizeInput($_POST['deliverables']),
        'payment_terms' => sanitizeInput($_POST['payment_terms']),
        'executed_by' => $user['id']
    ];
    
    $procurementObj->saveContractExecution($data);
    $procurementObj->updateStage($id, 'disbursement');
    
    setFlashMessage('success', 'Contract executed successfully!');
    redirect("view-details.php?id=$id");
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Execution - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-amber-50 via-slate-50 to-orange-50 min-h-screen">
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
        <div class="bg-gradient-to-br from-amber-600 to-orange-600 text-white rounded-3xl shadow-2xl p-8 mb-8">
            <h1 class="heading-font text-4xl font-bold mb-3">Contract Execution</h1>
            <p class="text-amber-100 text-lg mb-4">Award contract and set terms</p>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mt-4">
                <p class="heading-font text-xl font-bold mb-1"><?= htmlspecialchars($procurement['title']) ?></p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <form method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Winning Bidder</label>
                        <input type="text" name="winning_bidder" required
                               value="<?= htmlspecialchars($stageData['winning_bidder'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none transition-all"
                               placeholder="Company/Supplier Name">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Contract Amount (₱)</label>
                        <input type="number" name="contract_amount" step="0.01" required
                               value="<?= htmlspecialchars($stageData['contract_amount'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none transition-all text-lg font-semibold"
                               placeholder="0.00">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Contract Date</label>
                        <input type="date" name="contract_date" required
                               value="<?= htmlspecialchars($stageData['contract_date'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Contract Duration</label>
                        <input type="text" name="contract_duration" required
                               value="<?= htmlspecialchars($stageData['contract_duration'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none transition-all"
                               placeholder="e.g., 12 months, 2 years, etc.">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Deliverables</label>
                    <textarea name="deliverables" rows="4" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none transition-all"
                              placeholder="List of deliverables and milestones..."><?= htmlspecialchars($stageData['deliverables'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Payment Terms</label>
                    <textarea name="payment_terms" rows="4" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none transition-all"
                              placeholder="Payment schedule and terms..."><?= htmlspecialchars($stageData['payment_terms'] ?? '') ?></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t-2 border-slate-100">
                    <a href="view-details.php?id=<?= $id ?>" 
                       class="px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white py-3 px-6 rounded-xl heading-font text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        Execute Contract
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