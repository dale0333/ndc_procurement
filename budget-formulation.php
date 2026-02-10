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

// Get existing data
$stageData = $procurementObj->getStageData($id, 'budget_formulation');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'procurement_id' => $id,
        'budget_amount' => sanitizeInput($_POST['budget_amount']),
        'category' => sanitizeInput($_POST['category']),
        'funding_source' => sanitizeInput($_POST['funding_source']),
        'cost_breakdown' => sanitizeInput($_POST['cost_breakdown']),
        'justification' => sanitizeInput($_POST['justification']),
        'submitted_by' => $user['id'],
        'status' => 'pending'
    ];
    
    $procurementObj->saveBudgetFormulation($data);
    $procurementObj->updateStage($id, 'budget_review');
    
    setFlashMessage('success', 'Budget formulation submitted successfully!');
    redirect("view-details.php?id=$id");
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Formulation - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-slate-50 to-indigo-50 min-h-screen">
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
        <div class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-3xl shadow-2xl p-8 mb-8">
            <h1 class="heading-font text-4xl font-bold mb-3">Budget Formulation</h1>
            <p class="text-blue-100 text-lg mb-4">Define your budget requirements and justification</p>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mt-4">
                <p class="heading-font text-xl font-bold mb-1"><?= htmlspecialchars($procurement['title']) ?></p>
                <p class="text-blue-100"><?= htmlspecialchars($procurement['description']) ?></p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <form method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Budget Amount (₱)</label>
                        <input type="number" name="budget_amount" step="0.01" required
                               value="<?= htmlspecialchars($stageData['budget_amount'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all text-lg font-semibold"
                               placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Budget Category</label>
                        <select name="category" required
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all">
                            <option value="">Select Category</option>
                            <option value="CAPEX" <?= ($stageData['category'] ?? '') === 'CAPEX' ? 'selected' : '' ?>>Capital Expenditure (CAPEX)</option>
                            <option value="OPEX" <?= ($stageData['category'] ?? '') === 'OPEX' ? 'selected' : '' ?>>Operating Expenditure (OPEX)</option>
                            <option value="Infrastructure" <?= ($stageData['category'] ?? '') === 'Infrastructure' ? 'selected' : '' ?>>Infrastructure</option>
                            <option value="Supplies" <?= ($stageData['category'] ?? '') === 'Supplies' ? 'selected' : '' ?>>Supplies & Materials</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Funding Source</label>
                    <select name="funding_source" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all">
                        <option value="">Select Funding Source</option>
                        <option value="national_budget" <?= ($stageData['funding_source'] ?? '') === 'national_budget' ? 'selected' : '' ?>>National Budget</option>
                        <option value="local_budget" <?= ($stageData['funding_source'] ?? '') === 'local_budget' ? 'selected' : '' ?>>Local Government Budget</option>
                        <option value="special_fund" <?= ($stageData['funding_source'] ?? '') === 'special_fund' ? 'selected' : '' ?>>Special Fund</option>
                        <option value="grant" <?= ($stageData['funding_source'] ?? '') === 'grant' ? 'selected' : '' ?>>Grant/Donation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Cost Breakdown</label>
                    <textarea name="cost_breakdown" rows="5" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all"
                              placeholder="Itemize major cost components..."><?= htmlspecialchars($stageData['cost_breakdown'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Budget Justification</label>
                    <textarea name="justification" rows="6" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all"
                              placeholder="Explain the need for this procurement..."><?= htmlspecialchars($stageData['justification'] ?? '') ?></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t-2 border-slate-100">
                    <a href="dashboard.php" 
                       class="px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3 px-6 rounded-xl heading-font text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        Submit Budget Formulation
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