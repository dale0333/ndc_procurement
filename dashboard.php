<?php
require_once 'includes/Auth.php';
require_once 'includes/Procurement.php';
require_once 'includes/helpers.php';

$auth = new Auth();
$auth->requireLogin();

$procurement = new Procurement();
$user = $auth->getCurrentUser();

// Get statistics
$stats = $procurement->getStatistics();

// Get recent procurements
$procurements = $procurement->getAll();

// Handle delete procurement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $procurementId = $_POST['procurement_id'] ?? null;
    if ($procurementId) {
        // Check if user has permission to delete (creator or admin)
        $procDetails = $procurement->getById($procurementId);
        if ($procDetails && ($procDetails['created_by'] == $user['id'] || $user['role'] === 'admin')) {
            $result = $procurement->delete($procurementId);
            if ($result) {
                setFlashMessage('success', 'Procurement request deleted successfully!');
            } else {
                setFlashMessage('error', 'Failed to delete procurement request.');
            }
        } else {
            setFlashMessage('error', 'You do not have permission to delete this procurement.');
        }
    }
    redirect('dashboard.php');
}

// Handle new procurement creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $data = [
        'title' => sanitizeInput($_POST['title']),
        'description' => sanitizeInput($_POST['description']),
        'department' => sanitizeInput($_POST['department']),
        'priority' => sanitizeInput($_POST['priority']),
        'created_by' => $user['id'],
        'status' => 'in_progress'
    ];
    
    $id = $procurement->create($data);
    setFlashMessage('success', 'Procurement request created successfully!');
    redirect("budget-formulation.php?id=$id");
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Budget-Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .heading-font { font-family: 'Space Grotesk', sans-serif; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-in { animation: slideIn 0.5s ease-out forwards; }
        .stagger-1 { animation-delay: 0.1s; opacity: 0; }
        .stagger-2 { animation-delay: 0.2s; opacity: 0; }
        .stagger-3 { animation-delay: 0.3s; opacity: 0; }
        .stagger-4 { animation-delay: 0.4s; opacity: 0; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-lg border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="heading-font text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                        Budget-Procurement Management System
                    </span>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($user['full_name']) ?></p>
                        <p class="text-xs text-slate-500"><?= getRoleDisplayName($user['role']) ?></p>
                    </div>
                    <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <a href="logout.php" class="text-slate-600 hover:text-red-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-8">
        <!-- Flash Message -->
        <?php if ($flash): ?>
        <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500' ?> animate-slide-in">
            <p class="<?= $flash['type'] === 'success' ? 'text-green-700' : 'text-red-700' ?> font-medium">
                <?= htmlspecialchars($flash['message']) ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="mb-8 animate-slide-in stagger-1">
            <h1 class="heading-font text-4xl font-bold text-slate-800 mb-2">Dashboard</h1>
            <p class="text-slate-600 text-lg">Welcome back, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>! Here's your procurement overview.</p>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8 animate-slide-in stagger-2">
            <button onclick="document.getElementById('newProcurementModal').classList.remove('hidden')" 
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-semibold heading-font shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Procurement Request
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-blue-500 animate-slide-in stagger-2">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-slate-500 text-sm font-medium mb-1">Total Procurements</p>
                <p class="heading-font text-3xl font-bold text-slate-800"><?= number_format($stats['total']) ?></p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-amber-500 animate-slide-in stagger-3">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-amber-100 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-slate-500 text-sm font-medium mb-1">In Progress</p>
                <p class="heading-font text-3xl font-bold text-slate-800"><?= number_format($stats['in_progress']) ?></p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-green-500 animate-slide-in stagger-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-slate-500 text-sm font-medium mb-1">Completed</p>
                <p class="heading-font text-3xl font-bold text-slate-800"><?= number_format($stats['completed']) ?></p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-purple-500 animate-slide-in stagger-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-slate-500 text-sm font-medium mb-1">Total Value</p>
                <p class="heading-font text-2xl font-bold text-slate-800"><?= formatCurrency($stats['total_value']) ?></p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Stage Distribution -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="heading-font text-xl font-bold text-slate-800 mb-4">Active by Stage</h3>
                <canvas id="stageChart" height="200"></canvas>
            </div>

            <!-- Department Distribution -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="heading-font text-xl font-bold text-slate-800 mb-4">By Department</h3>
                <canvas id="departmentChart" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Procurements Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <h2 class="heading-font text-2xl font-bold text-slate-800">Recent Procurements</h2>
            </div>
            
            <?php if (empty($procurements)): ?>
                <div class="p-12 text-center">
                    <svg class="w-24 h-24 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-slate-500 text-lg">No procurement requests yet. Create your first one to get started!</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Stage</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($procurements as $proc): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-semibold text-slate-700"><?= htmlspecialchars($proc['reference_number']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($proc['title']) ?></p>
                                    <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars(substr($proc['description'], 0, 60)) ?>...</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600"><?= getDepartmentName($proc['department']) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-<?= getStageColor($proc['current_stage']) ?>-100 text-<?= getStageColor($proc['current_stage']) ?>-700">
                                        <?= getStageName($proc['current_stage']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= getStatusBadge($proc['status']) ?>">
                                        <?= ucwords(str_replace('_', ' ', $proc['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= getPriorityBadge($proc['priority']) ?>">
                                        <?= ucfirst($proc['priority']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-3">
                                        <a href="view-details.php?id=<?= $proc['id'] ?>" 
                                           class="text-blue-600 hover:text-blue-800 font-semibold">View</a>
                                        <?php if ($proc['status'] === 'in_progress'): ?>
                                            <?php 
                                                // Convert underscore to dash for the link
                                                $stageLink = str_replace('_', '-', $proc['current_stage']);
                                            ?>
                                            <a href="<?= $stageLink ?>.php?id=<?= $proc['id'] ?>" 
                                                class="text-green-600 hover:text-green-800 font-semibold">Continue</a>
                                        <?php endif; ?>
                                        <?php if ($proc['created_by'] == $user['id'] || $user['role'] === 'admin'): ?>
                                            <button onclick="confirmDelete(<?= $proc['id'] ?>, '<?= htmlspecialchars(addslashes($proc['title'])) ?>')" 
                                                    class="text-red-600 hover:text-red-800 font-semibold">Delete</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- New Procurement Modal -->
    <div id="newProcurementModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-8 animate-slide-in">
            <div class="flex justify-between items-center mb-6">
                <h2 class="heading-font text-3xl font-bold text-slate-800">New Procurement Request</h2>
                <button onclick="document.getElementById('newProcurementModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Project Title</label>
                    <input type="text" name="title" required 
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all"
                           placeholder="Enter project title">
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Description</label>
                    <textarea name="description" rows="4" required
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all"
                              placeholder="Describe the procurement need"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Department</label>
                        <select name="department" required
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all">
                            <option value="">Select Department</option>
                            <?php foreach (getDepartments() as $code => $name): ?>
                                <option value="<?= $code ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-700 font-semibold mb-2">Priority</label>
                        <select name="priority" required
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all">
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="button" onclick="document.getElementById('newProcurementModal').classList.add('hidden')"
                            class="flex-1 px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold heading-font shadow-lg transition-all">
                        Create Procurement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 animate-slide-in">
            <div class="flex justify-between items-center mb-6">
                <h2 class="heading-font text-2xl font-bold text-slate-800">Confirm Delete</h2>
                <button onclick="closeDeleteModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <p class="text-center text-slate-700 mb-2">
                    Are you sure you want to delete <span id="procurementTitle" class="font-semibold"></span>?
                </p>
                <p class="text-center text-sm text-slate-500">
                    This action cannot be undone. All associated data will be permanently deleted.
                </p>
            </div>
            
            <form id="deleteForm" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="procurement_id" id="deleteProcurementId">
                
                <div class="flex gap-4">
                    <button type="button" onclick="closeDeleteModal()"
                            class="flex-1 px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-semibold heading-font transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white rounded-xl font-semibold heading-font shadow-lg transition-all">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Stage Distribution Chart
        const stageCtx = document.getElementById('stageChart').getContext('2d');
        new Chart(stageCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_map('getStageName', array_column($stats['by_stage'], 'current_stage'))) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($stats['by_stage'], 'count')) ?>,
                    backgroundColor: [
                        '#3B82F6', '#A855F7', '#10B981', '#F59E0B', '#14B8A6', '#6366F1', '#F43F5E'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Department Chart
        const deptCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map('getDepartmentName', array_column($stats['by_department'], 'department'))) ?>,
                datasets: [{
                    label: 'Procurements',
                    data: <?= json_encode(array_column($stats['by_department'], 'count')) ?>,
                    backgroundColor: '#3B82F6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Delete confirmation functions
        let currentProcurementId = null;
        let currentProcurementTitle = null;

        function confirmDelete(procurementId, procurementTitle) {
            currentProcurementId = procurementId;
            currentProcurementTitle = procurementTitle;
            
            document.getElementById('procurementTitle').textContent = procurementTitle;
            document.getElementById('deleteProcurementId').value = procurementId;
            document.getElementById('deleteConfirmationModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteConfirmationModal').classList.add('hidden');
            currentProcurementId = null;
            currentProcurementTitle = null;
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
                document.getElementById('newProcurementModal').classList.add('hidden');
            }
        });
    </script>
</body>
</html>