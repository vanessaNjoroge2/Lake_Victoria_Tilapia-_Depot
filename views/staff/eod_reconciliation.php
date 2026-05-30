<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/ReportController.php';
require_once __DIR__ . '/../../includes/csrf.php';

$authController = new AuthController();
// Reconciliations are restricted to admins
$authController->requireRole(['admin']);

$reportController = new ReportController();
$error = '';
$success = '';

// Get date for EOD reconciliation
$date = $_GET['date'] ?? date('Y-m-d');

// Fetch Expected register figures for the chosen date
$eodData = $reportController->getExpectedEOD($date);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = "CSRF verification failed. Please try again.";
    } else {
        $actual_cash = floatval($_POST['actual_cash'] ?? 0.00);
        $actual_mpesa = floatval($_POST['actual_mpesa'] ?? 0.00);
        $actual_credit = floatval($_POST['actual_credit'] ?? 0.00);
        $notes = trim($_POST['notes'] ?? '');

        $reconData = [
            'closing_date' => $date,
            'cashier_id' => $_SESSION['user_id'],
            'expected_cash' => $eodData['expected_cash'],
            'actual_cash' => $actual_cash,
            'expected_mpesa' => $eodData['expected_mpesa'],
            'actual_mpesa' => $actual_mpesa,
            'expected_credit' => $eodData['expected_credit'],
            'actual_credit' => $actual_credit,
            'notes' => $notes
        ];

        if ($reportController->saveEODReconciliation($reconData)) {
            $success = "Register reconciliation closed successfully for " . date('d M Y', strtotime($date)) . "!";
            // Refresh data
            $eodData = $reportController->getExpectedEOD($date);
        } else {
            $error = "Failed to save EOD reconciliation. Please try again.";
        }
    }
}

// Fetch historical EOD reconciliation sessions
$history = $reportController->getEODHistory(30);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EOD Reconciliation - <?php echo SITE_NAME; ?></title>
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
            
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">End of Day (EOD) Reconciliation</h1>
                    <p class="text-gray-600">Reconcile expected sales with physical drawer and statement balances</p>
                </div>
                <!-- Date Selector Form -->
                <form method="GET" action="" class="flex items-center gap-3 bg-white p-2 rounded-xl border border-slate-200 shadow-sm text-xs font-bold">
                    <label class="text-slate-500 uppercase tracking-wider pl-2">Select Date</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" 
                           onchange="this.form.submit()"
                           class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                </form>
            </div>

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 font-semibold text-xs flex items-center">
                    <i class="fas fa-exclamation-circle mr-2 text-red-500 text-base"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 font-semibold text-xs flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500 text-base"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8 text-xs">
                
                <!-- Physical Cash Denomination Counter Card -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-1">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                        <i class="fas fa-coins text-amber-500 text-lg"></i>
                        <h3 class="text-slate-800 font-bold text-sm uppercase tracking-wider">Drawer Cash Calculator</h3>
                    </div>
                    <p class="text-[10px] text-slate-400 font-semibold mb-4 leading-relaxed">Enter counts for each note and coin denomination to automatically compute the total physical drawer cash.</p>
                    
                    <div class="space-y-3 font-semibold text-slate-700 max-h-[380px] overflow-y-auto pr-1">
                        <!-- 1000 Note -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 1,000 Note</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="1000" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                        <!-- 500 Note -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 500 Note</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="500" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                        <!-- 200 Note -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 200 Note</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="200" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                        <!-- 100 Note -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 100 Note</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="100" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                        <!-- 50 Note -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 50 Note</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="50" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                        <!-- 20 Coin/Note -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 20 Coin</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="20" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                        <!-- 10 Coin -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 10 Coin</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="10" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                        <!-- 5 Coin -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 5 Coin</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="5" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                        <!-- 1 Coin -->
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="w-20">Ksh 1 Coin</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400">x</span>
                                <input type="number" data-value="1" class="denom-input w-16 px-2 py-1 text-center bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Computed Total Display -->
                    <div class="mt-4 p-4 bg-slate-900 text-white rounded-xl border border-slate-800 flex justify-between items-center font-bold">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400">Calculated Cash:</span>
                        <span class="text-sm text-green-400" id="calcCashDisplay">Ksh 0.00</span>
                    </div>
                </div>

                <!-- Reconciliation Form Card -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2 flex flex-col justify-between">
                    <div class="w-full">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-balance-scale text-blue-600 text-lg"></i>
                                <h3 class="text-slate-800 font-bold text-sm uppercase tracking-wider">Reconcile Channels</h3>
                            </div>
                            <?php if ($eodData['reconciled']): ?>
                                <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-lg font-bold text-[10px] uppercase flex items-center">
                                    <i class="fas fa-circle-check mr-1"></i> Already Reconciled
                                </span>
                            <?php else: ?>
                                <span class="bg-amber-50 text-amber-700 px-3 py-1 rounded-lg font-bold text-[10px] uppercase flex items-center animate-pulse">
                                    <i class="fas fa-triangle-exclamation mr-1"></i> Reconcile Pending
                                </span>
                            <?php endif; ?>
                        </div>

                        <form id="reconciliationForm" method="POST" action="" class="space-y-6">
                            <?php echo csrf_field(); ?>
                            
                            <!-- expected vs actual tables -->
                            <div class="space-y-4">
                                <!-- Cash Reconciliation Line -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <div class="font-bold text-slate-700">
                                        <i class="fas fa-money-bill text-green-500 mr-1.5 text-base"></i>
                                        <span>Drawer Cash</span>
                                    </div>
                                    <div class="font-bold text-slate-500">
                                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 mb-0.5">Expected System</span>
                                        <span>Ksh <?php echo number_format($eodData['expected_cash'], 2); ?></span>
                                        <input type="hidden" id="expected_cash" value="<?php echo $eodData['expected_cash']; ?>">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Physical Actual</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="number" step="0.01" min="0" name="actual_cash" id="actual_cash" 
                                                   value="<?php echo htmlspecialchars($eodData['reconciled'] ? $eodData['reconciliation_details']['actual_cash'] : '0.00'); ?>"
                                                   <?php echo $eodData['reconciled'] ? 'readonly' : ''; ?>
                                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold" required>
                                            <button type="button" id="copyCalcCash" <?php echo $eodData['reconciled'] ? 'disabled' : ''; ?>
                                                    class="p-2 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300 hover:text-slate-800 disabled:opacity-50" title="Apply Drawer Calculator value">
                                                <i class="fas fa-paste"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-right font-bold" id="cashVarianceDisplay">
                                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 mb-0.5">Cash Variance</span>
                                        <span class="text-slate-700">Ksh 0.00</span>
                                    </div>
                                </div>

                                <!-- M-Pesa Reconciliation Line -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <div class="font-bold text-slate-700">
                                        <i class="fas fa-mobile-screen text-cyan-500 mr-1.5 text-base"></i>
                                        <span>M-Pesa Receipts</span>
                                    </div>
                                    <div class="font-bold text-slate-500">
                                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 mb-0.5">Expected System</span>
                                        <span>Ksh <?php echo number_format($eodData['expected_mpesa'], 2); ?></span>
                                        <input type="hidden" id="expected_mpesa" value="<?php echo $eodData['expected_mpesa']; ?>">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Statement Actual</label>
                                        <input type="number" step="0.01" min="0" name="actual_mpesa" id="actual_mpesa" 
                                               value="<?php echo htmlspecialchars($eodData['reconciled'] ? $eodData['reconciliation_details']['actual_mpesa'] : '0.00'); ?>"
                                               <?php echo $eodData['reconciled'] ? 'readonly' : ''; ?>
                                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold" required>
                                    </div>
                                    <div class="text-right font-bold" id="mpesaVarianceDisplay">
                                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 mb-0.5">Mpesa Variance</span>
                                        <span class="text-slate-700">Ksh 0.00</span>
                                    </div>
                                </div>

                                <!-- Credit Ledger Reconciliation Line -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <div class="font-bold text-slate-700">
                                        <i class="fas fa-users-viewfinder text-blue-500 mr-1.5 text-base"></i>
                                        <span>Wholesale Credit</span>
                                    </div>
                                    <div class="font-bold text-slate-500">
                                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 mb-0.5">Expected System</span>
                                        <span>Ksh <?php echo number_format($eodData['expected_credit'], 2); ?></span>
                                        <input type="hidden" id="expected_credit" value="<?php echo $eodData['expected_credit']; ?>">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Invoiced Actual</label>
                                        <input type="number" step="0.01" min="0" name="actual_credit" id="actual_credit" 
                                               value="<?php echo htmlspecialchars($eodData['reconciled'] ? $eodData['reconciliation_details']['actual_credit'] : '0.00'); ?>"
                                               <?php echo $eodData['reconciled'] ? 'readonly' : ''; ?>
                                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold" required>
                                    </div>
                                    <div class="text-right font-bold" id="creditVarianceDisplay">
                                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 mb-0.5">Credit Variance</span>
                                        <span class="text-slate-700">Ksh 0.00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes and remarks -->
                            <div>
                                <label for="notes" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Daily Reconciliation Auditing Notes
                                </label>
                                <textarea id="notes" name="notes" rows="2" placeholder="Record reason for variances, cashier drawer checks, or remarks..."
                                          <?php echo $eodData['reconciled'] ? 'readonly' : ''; ?>
                                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium"><?php echo htmlspecialchars($eodData['reconciled'] ? $eodData['reconciliation_details']['notes'] : ''); ?></textarea>
                            </div>

                            <!-- Reconcile Submit Panel -->
                            <?php if (!$eodData['reconciled']): ?>
                                <div class="pt-4 border-t border-slate-100 flex justify-end">
                                    <button type="submit"
                                            class="bg-blue-600 text-white px-6 py-3.5 rounded-xl hover:bg-blue-700 font-bold shadow-md shadow-blue-500/10 flex items-center">
                                        <i class="fas fa-lock mr-2"></i> Submit and Lock Reconciliation
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl font-semibold flex items-center justify-between">
                                    <span>
                                        <i class="fas fa-lock mr-1.5 text-base"></i> Closed & locked by admin: <strong><?php echo htmlspecialchars($eodData['reconciliation_details']['cashier_name']); ?></strong>
                                    </span>
                                    <span class="text-[10px] text-slate-400"><?php echo date('d M Y, g:i A', strtotime($eodData['reconciliation_details']['created_at'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

            </div>

            <!-- EOD Historical audit trail -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-xs">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                    <i class="fas fa-history text-purple-600 text-lg"></i>
                    <h3 class="text-slate-800 font-bold text-sm uppercase tracking-wider">Historical daily reconciliations (Last 30 Closings)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 uppercase font-bold">
                                <th class="pb-3">Reconciled Date</th>
                                <th class="pb-3">Auditor / Closed By</th>
                                <th class="pb-3 text-right">Cash Expected / Actual</th>
                                <th class="pb-3 text-right">Mpesa Expected / Actual</th>
                                <th class="pb-3 text-right">Credit Expected / Actual</th>
                                <th class="pb-3 text-right">Net Variances</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 font-semibold text-slate-700">
                            <?php if (empty($history)): ?>
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-slate-400">No historical register closings logged. Reconcile above to close today's register session.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3 text-slate-800 font-bold">
                                            <a href="?date=<?php echo $h['closing_date']; ?>" class="hover:underline text-blue-600">
                                                <?php echo date('d M Y', strtotime($h['closing_date'])); ?>
                                            </a>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex flex-col">
                                                <span><?php echo htmlspecialchars($h['cashier_name']); ?></span>
                                                <span class="text-[9px] text-slate-400">Checked: <?php echo date('d M Y H:i', strtotime($h['created_at'])); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex flex-col font-medium text-slate-500">
                                                <span>Exp: <?php echo number_format($h['expected_cash'], 2); ?></span>
                                                <span class="font-bold text-slate-800">Act: <?php echo number_format($h['actual_cash'], 2); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex flex-col font-medium text-slate-500">
                                                <span>Exp: <?php echo number_format($h['expected_mpesa'], 2); ?></span>
                                                <span class="font-bold text-slate-800">Act: <?php echo number_format($h['actual_mpesa'], 2); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex flex-col font-medium text-slate-500">
                                                <span>Exp: <?php echo number_format($h['expected_credit'], 2); ?></span>
                                                <span class="font-bold text-slate-800">Act: <?php echo number_format($h['actual_credit'], 2); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex flex-col text-[10px] font-bold">
                                                <?php 
                                                $cv = floatval($h['variance_cash']);
                                                $mv = floatval($h['variance_mpesa']);
                                                $crv = floatval($h['variance_credit']);
                                                
                                                echo $cv == 0 ? '<span class="text-green-600">Cash: Balanced</span>' : ($cv > 0 ? '<span class="text-blue-600">Cash: +'.number_format($cv, 2).'</span>' : '<span class="text-red-600">Cash: '.number_format($cv, 2).'</span>');
                                                echo $mv == 0 ? '<span class="text-green-600">Mpesa: Balanced</span>' : ($mv > 0 ? '<span class="text-blue-600">Mpesa: +'.number_format($mv, 2).'</span>' : '<span class="text-red-600">Mpesa: '.number_format($mv, 2).'</span>');
                                                echo $crv == 0 ? '<span class="text-green-600">Credit: Balanced</span>' : ($crv > 0 ? '<span class="text-blue-600">Credit: +'.number_format($crv, 2).'</span>' : '<span class="text-red-600">Credit: '.number_format($crv, 2).'</span>');
                                                ?>
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

    <!-- JS Calculator and Real-time Variance Handler -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const denomInputs = document.querySelectorAll(".denom-input");
            const calcCashDisplay = document.getElementById("calcCashDisplay");
            const actualCashInput = document.getElementById("actual_cash");
            const actualMpesaInput = document.getElementById("actual_mpesa");
            const actualCreditInput = document.getElementById("actual_credit");
            const copyCalcCashBtn = document.getElementById("copyCalcCash");

            const expectedCash = parseFloat(document.getElementById("expected_cash").value || 0);
            const expectedMpesa = parseFloat(document.getElementById("expected_mpesa").value || 0);
            const expectedCredit = parseFloat(document.getElementById("expected_credit").value || 0);

            const cashVarianceDisplay = document.getElementById("cashVarianceDisplay");
            const mpesaVarianceDisplay = document.getElementById("mpesaVarianceDisplay");
            const creditVarianceDisplay = document.getElementById("creditVarianceDisplay");

            // Calculate Drawer Calculator Cash
            function calculateCalcCash() {
                let total = 0;
                denomInputs.forEach(input => {
                    const value = parseFloat(input.getAttribute("data-value") || 0);
                    const qty = parseInt(input.value || 0);
                    total += value * qty;
                });
                calcCashDisplay.textContent = "Ksh " + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                return total;
            }

            // Calculate Variance
            function updateVariance() {
                // Cash
                const actualCash = parseFloat(actualCashInput.value || 0);
                const cashVar = actualCash - expectedCash;
                renderVariance(cashVarianceDisplay, "Cash Variance", cashVar);

                // Mpesa
                const actualMpesa = parseFloat(actualMpesaInput.value || 0);
                const mpesaVar = actualMpesa - expectedMpesa;
                renderVariance(mpesaVarianceDisplay, "Mpesa Variance", mpesaVar);

                // Credit
                const actualCredit = parseFloat(actualCreditInput.value || 0);
                const creditVar = actualCredit - expectedCredit;
                renderVariance(creditVarianceDisplay, "Credit Variance", creditVar);
            }

            function renderVariance(element, label, value) {
                let colorClass = "text-slate-700";
                let sign = "";
                if (value > 0) {
                    colorClass = "text-blue-600";
                    sign = "+";
                } else if (value < 0) {
                    colorClass = "text-red-600";
                } else {
                    colorClass = "text-green-600";
                }
                
                element.innerHTML = `
                    <span class="block text-[10px] uppercase tracking-wider text-slate-400 mb-0.5">${label}</span>
                    <span class="${colorClass}">${value === 0 ? "Balanced" : "Ksh " + sign + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}</span>
                `;
            }

            // Listen to calculator changes
            denomInputs.forEach(input => {
                input.addEventListener("input", calculateCalcCash);
            });

            // Copy computed cash to physical actual cash input
            if (copyCalcCashBtn) {
                copyCalcCashBtn.addEventListener("click", function () {
                    const calculatedTotal = calculateCalcCash();
                    actualCashInput.value = calculatedTotal.toFixed(2);
                    updateVariance();
                });
            }

            // Listen to actual inputs changes
            actualCashInput.addEventListener("input", updateVariance);
            actualMpesaInput.addEventListener("input", updateVariance);
            actualCreditInput.addEventListener("input", updateVariance);

            // Initial computation
            calculateCalcCash();
            updateVariance();
        });
    </script>
</body>
</html>
