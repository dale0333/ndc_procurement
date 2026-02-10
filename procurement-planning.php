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
if (!canAccessStage($user['role'], 'procurement_planning')) {
    setFlashMessage('error', 'You do not have permission to access this stage');
    redirect('dashboard.php');
}

// Get existing data
$stageData = $procurementObj->getStageData($id, 'procurement_planning');
$budgetData = $procurementObj->getStageData($id, 'budget_review');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'procurement_id' => $id,
        'procurement_method' => sanitizeInput($_POST['procurement_method']),
        'timeline' => sanitizeInput($_POST['timeline']),
        'technical_specs' => sanitizeInput($_POST['technical_specs']),
        'evaluation_criteria' => sanitizeInput($_POST['evaluation_criteria']),
        'bid_documents' => sanitizeInput($_POST['bid_documents']),
        'planned_by' => $user['id']
    ];
    
    $procurementObj->saveProcurementPlanning($data);
    $procurementObj->updateStage($id, 'contract_execution');
    
    setFlashMessage('success', 'Procurement planning completed successfully!');
    redirect("view-details.php?id=$id");
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Planning - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .heading-font { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 via-slate-50 to-indigo-50 min-h-screen">
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
        <div class="bg-gradient-to-br from-emerald-600 to-teal-600 text-white rounded-3xl shadow-2xl p-8 mb-8">
            <h1 class="heading-font text-4xl font-bold mb-3">Procurement Planning</h1>
            <p class="text-emerald-100 text-lg mb-4">Plan procurement method and specifications</p>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mt-4">
                <p class="heading-font text-xl font-bold mb-1"><?= htmlspecialchars($procurement['title']) ?></p>
                <p class="text-emerald-100">Budget: ₱ <?= number_format($budgetData['approved_amount'] ?? 0, 2) ?></p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <form method="POST" class="space-y-8">
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Procurement Method</label>
                    <select name="procurement_method" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-all">
                        <option value="">Select Method</option>
                        <option value="public_bidding" <?= ($stageData['procurement_method'] ?? '') === 'public_bidding' ? 'selected' : '' ?>>Public Bidding</option>
                        <option value="limited_source" <?= ($stageData['procurement_method'] ?? '') === 'limited_source' ? 'selected' : '' ?>>Limited Source Bidding</option>
                        <option value="direct_contracting" <?= ($stageData['procurement_method'] ?? '') === 'direct_contracting' ? 'selected' : '' ?>>Direct Contracting</option>
                        <option value="shopping" <?= ($stageData['procurement_method'] ?? '') === 'shopping' ? 'selected' : '' ?>>Shopping</option>
                        <option value="negotiated" <?= ($stageData['procurement_method'] ?? '') === 'negotiated' ? 'selected' : '' ?>>Negotiated Procurement</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Timeline</label>
                    <input type="text" name="timeline" required
                           value="<?= htmlspecialchars($stageData['timeline'] ?? '') ?>"
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-all"
                           placeholder="e.g., 30 days, 60 days, etc.">
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Technical Specifications</label>
                    <textarea name="technical_specs" rows="5" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-all"
                              placeholder="Detailed technical requirements and specifications..."><?= htmlspecialchars($stageData['technical_specs'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Evaluation Criteria</label>
                    <textarea name="evaluation_criteria" rows="4" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-all"
                              placeholder="Criteria for evaluating bids..."><?= htmlspecialchars($stageData['evaluation_criteria'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Bid Documents</label>
                    <textarea name="bid_documents" rows="4" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-all"
                              placeholder="List of required bid documents..."><?= htmlspecialchars($stageData['bid_documents'] ?? '') ?></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t-2 border-slate-100">
                    <a href="view-details.php?id=<?= $id ?>" 
                       class="px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white py-3 px-6 rounded-xl heading-font text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        Submit Procurement Plan
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