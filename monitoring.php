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
if (!canAccessStage($user['role'], 'monitoring')) {
    setFlashMessage('error', 'You do not have permission to access this stage');
    redirect('dashboard.php');
}

// Get existing data
$stageData = $procurementObj->getStageData($id, 'contract_execution');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'procurement_id' => $id,
        'monitoring_date' => sanitizeInput($_POST['monitoring_date']),
        'progress_percentage' => sanitizeInput($_POST['progress_percentage']),
        'deliverables_status' => sanitizeInput($_POST['deliverables_status']),
        'issues_identified' => sanitizeInput($_POST['issues_identified']),
        'recommendations' => sanitizeInput($_POST['recommendations']),
        'monitored_by' => $user['id']
    ];
    
    $procurementObj->saveMonitoring($data);
    
    // If progress is 100%, move to audit
    if ($_POST['progress_percentage'] == 100) {
        $procurementObj->updateStage($id, 'audit');
    }
    
    setFlashMessage('success', 'Monitoring report submitted successfully!');
    redirect("view-details.php?id=$id");
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-indigo-50 via-slate-50 to-blue-50 min-h-screen">
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
        <div class="bg-gradient-to-br from-indigo-600 to-blue-600 text-white rounded-3xl shadow-2xl p-8 mb-8">
            <h1 class="heading-font text-4xl font-bold mb-3">Monitoring & Reporting</h1>
            <p class="text-indigo-100 text-lg mb-4">Track progress and deliverables</p>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mt-4">
                <p class="heading-font text-xl font-bold mb-1"><?= htmlspecialchars($procurement['title']) ?></p>
                <?php if ($stageData): ?>
                    <p class="text-indigo-100">Contractor: <?= htmlspecialchars($stageData['winning_bidder']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <form method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Monitoring Date</label>
                        <input type="date" name="monitoring_date" required
                               value="<?= date('Y-m-d') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Progress Percentage</label>
                        <div class="flex items-center gap-2">
                            <input type="range" name="progress_percentage" min="0" max="100" step="1" required
                                   class="flex-1"
                                   value="0">
                            <span class="text-lg font-bold text-indigo-600 w-16 text-center">0%</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Drag slider to set progress</div>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Deliverables Status</label>
                    <textarea name="deliverables_status" rows="4" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all"
                              placeholder="Status of each deliverable..."></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Issues Identified</label>
                    <textarea name="issues_identified" rows="3"
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all"
                              placeholder="Any issues or challenges encountered..."></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Recommendations</label>
                    <textarea name="recommendations" rows="3"
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 focus:outline-none transition-all"
                              placeholder="Recommendations for improvement..."></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t-2 border-slate-100">
                    <a href="view-details.php?id=<?= $id ?>" 
                       class="px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white py-3 px-6 rounded-xl heading-font text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        Submit Monitoring Report
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Update percentage display
        const slider = document.querySelector('input[type="range"]');
        const percentage = document.querySelector('span');
        
        slider.addEventListener('input', function() {
            percentage.textContent = this.value + '%';
        });
    </script>
</body>
</html>