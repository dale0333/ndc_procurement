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
if (!canAccessStage($user['role'], 'audit')) {
    setFlashMessage('error', 'You do not have permission to access this stage');
    redirect('dashboard.php');
}

// Get existing data
$stageData = $procurementObj->getStageData($id, 'audits');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'procurement_id' => $id,
        'auditor_id' => $user['id'],
        'audit_date' => sanitizeInput($_POST['audit_date']),
        'compliance_status' => sanitizeInput($_POST['compliance_status']),
        'audit_findings' => sanitizeInput($_POST['audit_findings']),
        'recommendations' => sanitizeInput($_POST['recommendations']),
        'overall_rating' => sanitizeInput($_POST['overall_rating'])
    ];
    
    $procurementObj->saveAudit($data);
    
    setFlashMessage('success', 'Audit completed successfully! Procurement marked as completed.');
    redirect("view-details.php?id=$id");
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-rose-50 via-slate-50 to-pink-50 min-h-screen">
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
        <div class="bg-gradient-to-br from-rose-600 to-pink-600 text-white rounded-3xl shadow-2xl p-8 mb-8">
            <h1 class="heading-font text-4xl font-bold mb-3">Audit & Feedback</h1>
            <p class="text-rose-100 text-lg mb-4">Final compliance check and completion</p>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 mt-4">
                <p class="heading-font text-xl font-bold mb-1"><?= htmlspecialchars($procurement['title']) ?></p>
                <p class="text-rose-100">Final stage - Complete audit to finish procurement</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <form method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Audit Date</label>
                        <input type="date" name="audit_date" required
                               value="<?= date('Y-m-d') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Compliance Status</label>
                        <select name="compliance_status" required
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 focus:outline-none transition-all">
                            <option value="">Select Status</option>
                            <option value="fully_compliant">Fully Compliant</option>
                            <option value="mostly_compliant">Mostly Compliant</option>
                            <option value="partially_compliant">Partially Compliant</option>
                            <option value="non_compliant">Non-Compliant</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Overall Rating</label>
                    <select name="overall_rating" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 focus:outline-none transition-all">
                        <option value="">Select Rating</option>
                        <option value="excellent">Excellent</option>
                        <option value="satisfactory">Satisfactory</option>
                        <option value="needs_improvement">Needs Improvement</option>
                        <option value="unsatisfactory">Unsatisfactory</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Audit Findings</label>
                    <textarea name="audit_findings" rows="5" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 focus:outline-none transition-all"
                              placeholder="Summary of audit findings..."><?= htmlspecialchars($stageData['audit_findings'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Recommendations</label>
                    <textarea name="recommendations" rows="4" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 focus:outline-none transition-all"
                              placeholder="Recommendations for future procurements..."><?= htmlspecialchars($stageData['recommendations'] ?? '') ?></textarea>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                    <p class="text-blue-800 font-semibold">⚠️ Important Note</p>
                    <p class="text-blue-700 text-sm mt-1">Submitting this audit will mark the procurement as <strong>COMPLETED</strong>. This action cannot be undone.</p>
                </div>

                <div class="flex gap-4 pt-6 border-t-2 border-slate-100">
                    <a href="view-details.php?id=<?= $id ?>" 
                       class="px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white py-3 px-6 rounded-xl heading-font text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        Complete Audit & Finish Procurement
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>