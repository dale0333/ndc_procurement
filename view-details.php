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

// Get all stage data
$stageData = $procurementObj->getAllStageData($id);

// Get comments
$comments = $procurementObj->getComments($id);

// Get activity log
$activities = $procurementObj->getActivityLog($id);

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($procurement['title']) ?> - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .heading-font { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <a href="dashboard.php" class="flex items-center gap-2 text-slate-600 hover:text-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="font-semibold">Back to Dashboard</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600"><?= htmlspecialchars($user['full_name']) ?></span>
                    <a href="logout.php" class="text-slate-600 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-8">
        <!-- Flash Message -->
        <?php if ($flash): ?>
        <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500' ?>">
            <p class="<?= $flash['type'] === 'success' ? 'text-green-700' : 'text-red-700' ?> font-medium">
                <?= htmlspecialchars($flash['message']) ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Header Card -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-3xl shadow-2xl p-8 mb-8">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold">
                            <?= htmlspecialchars($procurement['reference_number']) ?>
                        </span>
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold">
                            <?= getDepartmentName($procurement['department']) ?>
                        </span>
                    </div>
                    <h1 class="heading-font text-4xl font-bold mb-3"><?= htmlspecialchars($procurement['title']) ?></h1>
                    <p class="text-blue-100 text-lg mb-4"><?= htmlspecialchars($procurement['description']) ?></p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <p class="text-blue-200 text-sm">Status</p>
                            <p class="text-xl font-bold"><?= ucwords(str_replace('_', ' ', $procurement['status'])) ?></p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <p class="text-blue-200 text-sm">Current Stage</p>
                            <p class="text-xl font-bold"><?= getStageName($procurement['current_stage']) ?></p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <p class="text-blue-200 text-sm">Priority</p>
                            <p class="text-xl font-bold"><?= ucfirst($procurement['priority']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow Navigation -->
        <div class="mb-8 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="heading-font text-xl font-bold text-slate-800 mb-4">Workflow Progress</h3>
            <div class="grid grid-cols-7 gap-2">
                <?php 
                $stages = ['budget-formulation', 'budget-review', 'procurement-planning', 'contract-execution', 'disbursement', 'monitoring', 'audit'];
                foreach ($stages as $idx => $stage): 
                    // Convert dash back to underscore for database comparison
                    $stageDbName = str_replace('-', '_', $stage);
                    $isCurrent = $stageDbName === $procurement['current_stage'];
                    $isCompleted = array_search($stageDbName, array_map(function($s) { 
                        return str_replace('-', '_', $s); 
                    }, $stages)) < array_search($procurement['current_stage'], array_map(function($s) { 
                        return str_replace('-', '_', $s); 
                    }, $stages));
                ?>
                <a href="<?= $stage ?>.php?id=<?= $id ?>" class="text-center block">
                    <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-2 
                        <?= $isCurrent ? 'bg-blue-600 text-white ring-4 ring-blue-200' : 
                           ($isCompleted ? 'bg-green-500 text-white' : 'bg-slate-200 text-slate-600') ?>">
                        <?= $idx + 1 ?>
                    </div>
                    <p class="text-xs <?= $isCurrent ? 'text-blue-600 font-semibold' : 'text-slate-600' ?>">
                        <?= getStageName($stageDbName) ?>
                    </p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Stage Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Budget Formulation -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="heading-font text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">1</span>
                    Budget Formulation
                </h3>
                <?php if ($stageData['budget_formulation']): ?>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-slate-500">Budget Amount</p>
                            <p class="font-semibold text-lg">₱ <?= number_format($stageData['budget_formulation']['budget_amount'], 2) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Category</p>
                            <p class="font-semibold"><?= htmlspecialchars($stageData['budget_formulation']['category']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Funding Source</p>
                            <p class="font-semibold"><?= htmlspecialchars(str_replace('_', ' ', $stageData['budget_formulation']['funding_source'])) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-slate-400 italic">Not started</p>
                <?php endif; ?>
            </div>

            <!-- Budget Review -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="heading-font text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center">2</span>
                    Budget Review
                </h3>
                <?php if ($stageData['budget_review']): ?>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-slate-500">Approval Status</p>
                            <p class="font-semibold"><?= htmlspecialchars(str_replace('_', ' ', $stageData['budget_review']['approval_status'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Approved Amount</p>
                            <p class="font-semibold text-lg">₱ <?= number_format($stageData['budget_review']['approved_amount'] ?? 0, 2) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Review Date</p>
                            <p class="font-semibold"><?= date('M d, Y', strtotime($stageData['budget_review']['review_date'])) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-slate-400 italic">Pending</p>
                <?php endif; ?>
            </div>

            <!-- Procurement Planning -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="heading-font text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">3</span>
                    Procurement Planning
                </h3>
                <?php if ($stageData['procurement_planning']): ?>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-slate-500">Procurement Method</p>
                            <p class="font-semibold"><?= htmlspecialchars(str_replace('_', ' ', $stageData['procurement_planning']['procurement_method'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Timeline</p>
                            <p class="font-semibold"><?= htmlspecialchars($stageData['procurement_planning']['timeline']) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-slate-400 italic">Not started</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="heading-font text-xl font-bold text-slate-800 mb-4">Comments & Notes</h3>
            <?php if (empty($comments)): ?>
                <p class="text-slate-400 italic">No comments yet</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($comments as $comment): ?>
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="flex justify-between items-start mb-1">
                            <p class="font-semibold"><?= htmlspecialchars($comment['full_name']) ?></p>
                            <p class="text-sm text-slate-500"><?= timeAgo($comment['created_at']) ?></p>
                        </div>
                        <p class="text-slate-700"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                        <p class="text-xs text-slate-500 mt-1">Stage: <?= getStageName($comment['stage']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Activity Log -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="heading-font text-xl font-bold text-slate-800 mb-4">Activity Log</h3>
            <div class="space-y-3">
                <?php foreach ($activities as $activity): ?>
                <div class="flex items-start gap-3 pb-3 border-b border-slate-100 last:border-0">
                    <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-slate-700">
                            <span class="font-semibold"><?= htmlspecialchars($activity['full_name']) ?></span>
                            <?= htmlspecialchars($activity['description'] ?? $activity['action']) ?>
                        </p>
                        <p class="text-xs text-slate-500"><?= timeAgo($activity['created_at']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>