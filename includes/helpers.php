<?php
// Helper Functions for Budget-Procurement Management System

function getStageName($stage) {
    $names = [
        'budget_formulation' => 'Budget Formulation',
        'budget_review' => 'Budget Review & Approval',
        'procurement_planning' => 'Procurement Planning',
        'contract_execution' => 'Contract Execution',
        'disbursement' => 'Disbursement & Payment',
        'monitoring' => 'Monitoring & Reporting',
        'audit' => 'Audit & Feedback'
    ];
    return $names[$stage] ?? ucwords(str_replace('_', ' ', $stage));
}

function getStageColor($stage) {
    $colors = [
        'budget_formulation' => 'blue',
        'budget_review' => 'purple',
        'procurement_planning' => 'emerald',
        'contract_execution' => 'amber',
        'disbursement' => 'teal',
        'monitoring' => 'indigo',
        'audit' => 'rose'
    ];
    return $colors[$stage] ?? 'slate';
}

function getStatusBadge($status) {
    $badges = [
        'draft' => 'bg-gray-100 text-gray-700',
        'in_progress' => 'bg-blue-100 text-blue-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'on_hold' => 'bg-yellow-100 text-yellow-700'
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-700';
}

function getPriorityBadge($priority) {
    $badges = [
        'low' => 'bg-gray-100 text-gray-800',
        'medium' => 'bg-blue-100 text-blue-800',
        'high' => 'bg-amber-100 text-amber-800',
        'urgent' => 'bg-red-100 text-red-800'
    ];
    return $badges[$priority] ?? $badges['medium'];
}

function formatCurrency($amount) {
    return '₱ ' . number_format($amount ?? 0, 2);
}

function timeAgo($datetime) {
    if (empty($datetime)) return 'N/A';
    
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = round($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = round($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = round($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Administrator',
        'budget_officer' => 'Budget Officer',
        'procurement_officer' => 'Procurement Officer',
        'auditor' => 'Auditor',
        'department_head' => 'Department Head',
        'finance_officer' => 'Finance Officer'
    ];
    return $roles[$role] ?? ucwords(str_replace('_', ' ', $role));
}

function getStageProgress($stage) {
    $stages = [
        'budget_formulation' => 14.3,
        'budget_review' => 28.6,
        'procurement_planning' => 42.9,
        'contract_execution' => 57.1,
        'disbursement' => 71.4,
        'monitoring' => 85.7,
        'audit' => 100
    ];
    return $stages[$stage] ?? 0;
}

function canAccessStage($userRole, $stage) {
    $permissions = [
        'budget_formulation' => ['admin', 'department_head', 'budget_officer'],
        'budget_review' => ['admin', 'budget_officer'],
        'procurement_planning' => ['admin', 'procurement_officer'],
        'contract_execution' => ['admin', 'procurement_officer'],
        'disbursement' => ['admin', 'finance_officer'],
        'monitoring' => ['admin', 'procurement_officer', 'department_head'],
        'audit' => ['admin', 'auditor']
    ];
    
    return in_array($userRole, $permissions[$stage] ?? []);
}

function getDepartments() {
    return [
        'ogm' => 'Office of the General Manager',
        'spg' => 'Special Projects Group',
        'csg' => 'Corporate Support Group',
        'fsg' => 'Finance and Subsidiaries Group',
        'amg' => 'Asset Management Group',
        'bdg' => 'Business Development Group',
        'ccg' => 'Corporate Communications Group',
        'legal' => 'Legal Department',
        'cpd' => 'Corporate Planning Department'
    ];
}

function getDepartmentName($code) {
    $departments = getDepartments();
    return $departments[$code] ?? $code;
}

function getDepartmentCode($name) {
    $departments = getDepartments();
    return array_search($name, $departments) ?? $name;
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}
?>