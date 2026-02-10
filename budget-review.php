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
if (!canAccessStage($user['role'], 'budget_review')) {
    setFlashMessage('error', 'You do not have permission to access this stage');
    redirect('dashboard.php');
}

// Get existing data
$stageData = $procurementObj->getStageData($id, 'budget_review');
$budgetData = $procurementObj->getStageData($id, 'budget_formulation');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'procurement_id' => $id,
        'reviewer_id' => $user['id'],
        'review_date' => date('Y-m-d'),
        'approval_status' => sanitizeInput($_POST['approval_status']),
        'approved_amount' => sanitizeInput($_POST['approved_amount']),
        'review_comments' => sanitizeInput($_POST['review_comments'])
    ];
    
    $procurementObj->saveBudgetReview($data);
    $procurementObj->updateStage($id, 'procurement_planning');
    
    setFlashMessage('success', 'Budget review completed successfully!');
    redirect("view-details.php?id=$id");
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Review - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .heading-font { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-slate-50 to-indigo-50 min-h-screen">
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
        <div class="bg-gradient-to-br from-purple-600 to-indigo-600 text-white rounded-3xl shadow-2xl p-8 mb-8">
            <h1 class="heading-font text-4xl font-bold mb-3">Budget Review & Approval</h1>
            <p class="text-purple-100 text-lg mb-4">Review and approve budget formulation</p>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mt-4">
                <p class="heading-font text-xl font-bold mb-1"><?= htmlspecialchars($procurement['title']) ?></p>
                <p class="text-purple-100"><?= htmlspecialchars($procurement['description']) ?></p>
            </div>
        </div>

        <!-- Budget Summary -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="heading-font text-xl font-bold text-slate-800 mb-4">Budget Formulation Summary</h3>
            <?php if ($budgetData): ?>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-slate-500">Requested Amount</p>
                    <p class="text-2xl font-bold text-slate-800">₱ <?= number_format($budgetData['budget_amount'], 2) ?></p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Category</p>
                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($budgetData['category']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Funding Source</p>
                    <p class="font-semibold text-slate-800"><?= htmlspecialchars(str_replace('_', ' ', $budgetData['funding_source'])) ?></p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Submitted By</p>
                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($procurement['created_by_name']) ?></p>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-slate-500">Justification</p>
                <p class="text-slate-700 mt-1"><?= nl2br(htmlspecialchars($budgetData['justification'])) ?></p>
            </div>
            <?php else: ?>
                <p class="text-slate-400 italic">No budget formulation submitted yet.</p>
            <?php endif; ?>
        </div>

        <!-- Review Form -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <form method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Approval Status</label>
                        <select name="approval_status" required
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition-all">
                            <option value="">Select Status</option>
                            <option value="approved" <?= ($stageData['approval_status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="approved_with_modifications" <?= ($stageData['approval_status'] ?? '') === 'approved_with_modifications' ? 'selected' : '' ?>>Approved with Modifications</option>
                            <option value="pending_revision" <?= ($stageData['approval_status'] ?? '') === 'pending_revision' ? 'selected' : '' ?>>Pending Revision</option>
                            <option value="rejected" <?= ($stageData['approval_status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Approved Amount (₱)</label>
                        <input type="number" name="approved_amount" step="0.01" required
                               value="<?= htmlspecialchars($stageData['approved_amount'] ?? $budgetData['budget_amount'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition-all text-lg font-semibold"
                               placeholder="0.00">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Review Comments</label>
                    <textarea name="review_comments" rows="6" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition-all"
                              placeholder="Provide your review comments and recommendations..."><?= htmlspecialchars($stageData['review_comments'] ?? '') ?></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t-2 border-slate-100">
                    <a href="view-details.php?id=<?= $id ?>" 
                       class="px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white py-3 px-6 rounded-xl heading-font text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        Submit Budget Review
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