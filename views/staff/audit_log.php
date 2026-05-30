<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/AuditController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

$authController = new AuthController();
// Audit log viewing is restricted to admin and staff
$authController->requireRole(['admin', 'staff']);

$auditController = new AuditController();
$userController = new UserController();

// Fetch staff members for filters dropdown
$staffList = $userController->getAllStaff();

// Gather filter params
$filters = [
    'user_id' => $_GET['user_id'] ?? '',
    'action' => $_GET['action'] ?? '',
    'table_affected' => $_GET['table_affected'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Fetch matching audit logs
$logs = $auditController->getLogs($filters);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Audit Log - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar Layout -->
    <?php include '../includes/staff_sidebar.php'; ?>

    <!-- Main Workspace -->
    <div class="flex-1 ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Security Audit Log</h1>
                    <p class="text-gray-600">Chronological trail of system modifications, checkouts, and staff access</p>
                </div>
                <div class="text-xs font-semibold text-slate-500 bg-white border border-slate-200 shadow-sm px-4 py-2.5 rounded-xl flex items-center gap-1.5">
                    <i class="fas fa-shield-halved text-blue-600 text-sm"></i>
                    <span>Total Logs Analyzed: <strong><?php echo count($logs); ?></strong></span>
                </div>
            </div>

            <!-- Filter Panel Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 text-xs">
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end font-bold">
                    
                    <!-- Cashier filter -->
                    <div>
                        <label for="user_id" class="block text-[10px] text-slate-400 uppercase tracking-wider mb-1">Audit Cashier/Staff</label>
                        <select name="user_id" id="user_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-slate-700">
                            <option value="">-- All Cashiers --</option>
                            <?php foreach ($staffList as $staff): ?>
                                <option value="<?php echo $staff['id']; ?>" <?php echo $filters['user_id'] == $staff['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($staff['full_name']); ?> (@<?php echo htmlspecialchars($staff['username']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Table affected filter -->
                    <div>
                        <label for="table_affected" class="block text-[10px] text-slate-400 uppercase tracking-wider mb-1">Affected Operation</label>
                        <select name="table_affected" id="table_affected" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-slate-700">
                            <option value="">-- All Operations --</option>
                            <option value="fish" <?php echo $filters['table_affected'] === 'fish' ? 'selected' : ''; ?>>fish (Inventory Product)</option>
                            <option value="pos_sales" <?php echo $filters['table_affected'] === 'pos_sales' ? 'selected' : ''; ?>>pos_sales (POS Checkout)</option>
                            <option value="debt_ledger" <?php echo $filters['table_affected'] === 'debt_ledger' ? 'selected' : ''; ?>>debt_ledger (Wholesale Ledger)</option>
                            <option value="frying_batches" <?php echo $filters['table_affected'] === 'frying_batches' ? 'selected' : ''; ?>>frying_batches (Frying Batches)</option>
                            <option value="wastage_log" <?php echo $filters['table_affected'] === 'wastage_log' ? 'selected' : ''; ?>>wastage_log (Wastage Log)</option>
                            <option value="stock_deliveries" <?php echo $filters['table_affected'] === 'stock_deliveries' ? 'selected' : ''; ?>>stock_deliveries (Inbound Deliveries)</option>
                            <option value="register_closings" <?php echo $filters['table_affected'] === 'register_closings' ? 'selected' : ''; ?>>register_closings (EOD Closing)</option>
                            <option value="users" <?php echo $filters['table_affected'] === 'users' ? 'selected' : ''; ?>>users (Staff Profile/Lockouts)</option>
                        </select>
                    </div>

                    <!-- Search query text field -->
                    <div>
                        <label for="action" class="block text-[10px] text-slate-400 uppercase tracking-wider mb-1">Action Keywords</label>
                        <input type="text" name="action" id="action" value="<?php echo htmlspecialchars($filters['action']); ?>" placeholder="e.g. login, void, wastage" 
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    </div>

                    <!-- Date range inputs -->
                    <div>
                        <label for="date_from" class="block text-[10px] text-slate-400 uppercase tracking-wider mb-1">Audit From</label>
                        <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>" 
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                    </div>
                    
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <label for="date_to" class="block text-[10px] text-slate-400 uppercase tracking-wider mb-1">Audit To</label>
                            <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>" 
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                        </div>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl transition flex items-center justify-center shadow-md shadow-blue-500/10">
                            <i class="fas fa-search text-sm"></i>
                        </button>
                        <a href="audit_log.php" 
                           class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl flex items-center justify-center transition">
                            <i class="fas fa-undo text-sm"></i>
                        </a>
                    </div>

                </form>
            </div>

            <!-- Audit Trail Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 uppercase font-bold">
                                <th class="pb-3 w-40">Timestamp</th>
                                <th class="pb-3 w-48">Auditor/Staff Member</th>
                                <th class="pb-3 w-60">System Action</th>
                                <th class="pb-3 w-44">Target Area</th>
                                <th class="pb-3 text-right">Access Device / Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 font-semibold text-slate-700">
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">No matching system audit entries found under these filters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <!-- Timestamp -->
                                        <td class="py-4 text-slate-500 font-medium">
                                            <?php echo date('d M Y', strtotime($log['created_at'])); ?><br>
                                            <span class="text-[10px] font-bold text-slate-400"><?php echo date('g:i:s A', strtotime($log['created_at'])); ?></span>
                                        </td>
                                        
                                        <!-- Auditor -->
                                        <td class="py-4">
                                            <div class="flex flex-col">
                                                <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($log['full_name'] ?? 'System/Anonymous'); ?></span>
                                                <?php if ($log['username']): ?>
                                                    <span class="text-[9px] text-slate-400">@<?php echo htmlspecialchars($log['username']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Action -->
                                        <td class="py-4">
                                            <div class="flex flex-col gap-1 pr-4">
                                                <span class="text-slate-900 font-bold text-xs"><?php echo htmlspecialchars($log['action']); ?></span>
                                                
                                                <!-- Clickable details values JSON if exists -->
                                                <?php if ($log['old_values'] || $log['new_values']): ?>
                                                    <div class="mt-1">
                                                        <details class="group">
                                                            <summary class="cursor-pointer text-[10px] text-blue-600 hover:text-blue-800 font-bold list-none flex items-center gap-1 focus:outline-none">
                                                                <i class="fas fa-caret-right group-open:rotate-90 transition"></i>
                                                                <span>Audit Details JSON</span>
                                                            </summary>
                                                            <div class="mt-2 space-y-1.5 p-3 bg-slate-950 text-emerald-400 rounded-xl font-mono text-[9px] leading-relaxed border border-slate-900 max-w-lg overflow-x-auto shadow-inner">
                                                                <?php if ($log['old_values'] && $log['old_values'] !== 'null'): ?>
                                                                    <div>
                                                                        <span class="text-slate-400 font-bold uppercase tracking-wider block mb-0.5">PREVIOUS STATE:</span>
                                                                        <pre><?php echo htmlspecialchars(json_encode(json_decode($log['old_values']), JSON_PRETTY_PRINT)); ?></pre>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if ($log['new_values'] && $log['new_values'] !== 'null'): ?>
                                                                    <div class="<?php echo ($log['old_values'] && $log['old_values'] !== 'null') ? 'mt-3 pt-3 border-t border-slate-800' : ''; ?>">
                                                                        <span class="text-green-500 font-bold uppercase tracking-wider block mb-0.5">NEW STATE:</span>
                                                                        <pre><?php echo htmlspecialchars(json_encode(json_decode($log['new_values']), JSON_PRETTY_PRINT)); ?></pre>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </details>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Target table + record ID -->
                                        <td class="py-4">
                                            <?php if ($log['table_affected']): ?>
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold text-center w-max uppercase tracking-wider
                                                        <?php echo match ($log['table_affected']) {
                                                            'pos_sales' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                                            'fish' => 'bg-blue-50 text-blue-700 border border-blue-100',
                                                            'users' => 'bg-purple-50 text-purple-700 border border-purple-100',
                                                            'frying_batches' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                                            'wastage_log' => 'bg-red-50 text-red-700 border border-red-100',
                                                            'stock_deliveries' => 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                                                            'register_closings' => 'bg-teal-50 text-teal-700 border border-teal-100',
                                                            'debt_ledger' => 'bg-sky-50 text-sky-700 border border-sky-100',
                                                            default => 'bg-gray-50 text-gray-700 border border-gray-100'
                                                        }; ?>">
                                                        <?php echo htmlspecialchars($log['table_affected']); ?>
                                                    </span>
                                                    <?php if ($log['record_id']): ?>
                                                        <span class="text-[9px] text-slate-400 font-bold">Record ID: #<?php echo $log['record_id']; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Device/IP details -->
                                        <td class="py-4 text-right">
                                            <div class="flex flex-col items-end gap-0.5 text-[10px] text-slate-500 font-medium">
                                                <span class="font-bold text-slate-700"><i class="fas fa-network-wired mr-1 text-slate-400"></i> IP: <?php echo htmlspecialchars($log['ip_address']); ?></span>
                                                <span class="max-w-[200px] truncate text-[9px] text-slate-400" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                                    <?php 
                                                    $ua = $log['user_agent'];
                                                    if (strpos($ua, 'Chrome') !== false) {
                                                        echo '<i class="fab fa-chrome mr-1"></i> Google Chrome';
                                                    } elseif (strpos($ua, 'Firefox') !== false) {
                                                        echo '<i class="fab fa-firefox mr-1"></i> Mozilla Firefox';
                                                    } elseif (strpos($ua, 'Safari') !== false) {
                                                        echo '<i class="fab fa-safari mr-1"></i> Apple Safari';
                                                    } else {
                                                        echo '<i class="fas fa-desktop mr-1"></i> ' . substr($ua, 0, 20) . '...';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
