<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/sanitize.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle quick registration of a new Wholesale Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_wholesale') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed.';
    } else {
        $name = sanitize_string($_POST['full_name'] ?? '');
        $phone = sanitize_string($_POST['phone'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : null;
        $address = sanitize_string($_POST['address'] ?? '');
        $credit_limit = sanitize_decimal($_POST['credit_limit'] ?? 0);

        if (empty($name) || empty($phone)) {
            $error = 'Full name and Phone number are required.';
        } else {
            try {
                // Generate a dummy username & secure hash for offline customer profile
                $tempUsername = 'ws_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . '_' . mt_rand(100, 999);
                $dummyPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

                // Check if username/phone already exists
                $chkQ = "SELECT id FROM users WHERE phone = :phone OR username = :username";
                $chkS = $db->prepare($chkQ);
                $chkS->execute([':phone' => $phone, ':username' => $tempUsername]);
                
                if ($chkS->rowCount() > 0) {
                    $error = 'A user profile with this phone number or name already exists.';
                } else {
                    $insQ = "INSERT INTO users (username, email, password, full_name, phone, address, role, customer_type, credit_limit, outstanding_balance) 
                             VALUES (:username, :email, :password, :name, :phone, :address, 'customer', 'wholesale', :limit, 0.00)";
                    $insS = $db->prepare($insQ);
                    $insS->execute([
                        ':username' => $tempUsername,
                        ':email' => $email,
                        ':password' => $dummyPassword,
                        ':name' => $name,
                        ':phone' => $phone,
                        ':address' => $address,
                        ':limit' => $credit_limit
                    ]);

                    // Log activity
                    require_once __DIR__ . '/../../controllers/AuditController.php';
                    AuditController::logActivity(
                        $_SESSION['user_id'],
                        "wholesale_customer_registered",
                        "users",
                        $db->lastInsertId(),
                        null,
                        ['full_name' => $name, 'credit_limit' => $credit_limit]
                    );

                    $message = 'Wholesale customer registered successfully!';
                }
            } catch (Exception $e) {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}

// Fetch stats
$totalCustomers = 0;
$totalOutstandingDebt = 0.00;
$highestDebtor = 'N/A';
$highestDebtAmount = 0.00;

try {
    // Basic stats
    $statsQ = "SELECT COUNT(*) as cnt, SUM(outstanding_balance) as tot_debt FROM users WHERE role='customer' AND customer_type='wholesale'";
    $statsS = $db->prepare($statsQ);
    $statsS->execute();
    $stats = $statsS->fetch(PDO::FETCH_ASSOC);
    $totalCustomers = $stats['cnt'] ?? 0;
    $totalOutstandingDebt = $stats['tot_debt'] ?? 0.00;

    // Highest Debtor
    $highQ = "SELECT full_name, outstanding_balance FROM users WHERE role='customer' AND customer_type='wholesale' ORDER BY outstanding_balance DESC LIMIT 1";
    $highS = $db->prepare($highQ);
    $highS->execute();
    if ($high = $highS->fetch(PDO::FETCH_ASSOC)) {
        if ($high['outstanding_balance'] > 0) {
            $highestDebtor = $high['full_name'];
            $highestDebtAmount = $high['outstanding_balance'];
        }
    }

    // Load Wholesale Customer List
    $custQ = "SELECT * FROM users WHERE role='customer' AND customer_type='wholesale' ORDER BY outstanding_balance DESC, full_name ASC";
    $custS = $db->prepare($custQ);
    $custS->execute();
    $customers = $custS->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'Error loading page data: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wholesale Ledger - <?php echo SITE_NAME; ?></title>
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

    <!-- Main Content Panel -->
    <div class="flex-1 ml-64 p-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Wholesale Customer Ledger</h1>
                <p class="text-gray-600">Track outstanding balances, manage credit terms, and process repayments</p>
            </div>
            
            <button onclick="toggleRegisterModal(true)" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 shadow-md transition duration-150 flex items-center">
                <i class="fas fa-user-plus mr-2"></i> Register Account
            </button>
        </div>

        <!-- Alert messages -->
        <?php if (!empty($message)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r shadow-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r shadow-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- KPI Stats Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-blue-100 text-blue-600 rounded-xl mr-4">
                    <i class="fas fa-users-viewfinder text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Wholesale Buyers</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $totalCustomers; ?> accounts</h3>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-red-100 text-red-600 rounded-xl mr-4">
                    <i class="fas fa-hand-holding-dollar text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Outstanding System Debt</span>
                    <h3 class="text-2xl font-bold text-red-600 mt-1">Ksh <?php echo number_format($totalOutstandingDebt, 2); ?></h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-amber-100 text-amber-600 rounded-xl mr-4">
                    <i class="fas fa-crown text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Highest Debtor</span>
                    <h3 class="text-base font-bold text-slate-800 truncate mt-1 max-w-[200px]" title="<?php echo htmlspecialchars($highestDebtor); ?>">
                        <?php echo htmlspecialchars($highestDebtor); ?>
                    </h3>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">Ksh <?php echo number_format($highestDebtAmount, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Ledger Table List Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex flex-wrap justify-between items-center gap-4">
                <h3 class="font-bold text-slate-800 text-lg">Active Wholesale Ledgers</h3>
                <div class="relative w-72">
                    <i class="fas fa-search absolute left-3 top-3.5 text-slate-400 text-sm"></i>
                    <input type="text" id="ledger-search" placeholder="Search customer name or phone..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="customers-table">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-4 px-6">Customer Details</th>
                            <th class="py-4 px-6">Phone Number</th>
                            <th class="py-4 px-6 text-right">Credit Limit</th>
                            <th class="py-4 px-6 text-right">Outstanding Debt</th>
                            <th class="py-4 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-users-slash text-4xl mb-3"></i>
                                    <p class="text-sm">No wholesale buyer profiles registered yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $c): ?>
                                <tr class="hover:bg-slate-50/80 transition duration-150">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($c['full_name']); ?></p>
                                        <p class="text-slate-400 text-[10px]"><?php echo htmlspecialchars($c['email'] ?? 'No email address'); ?></p>
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-600">
                                        <?php echo htmlspecialchars($c['phone']); ?>
                                    </td>
                                    <td class="py-4 px-6 text-right font-bold text-slate-700">
                                        Ksh <?php echo number_format($c['credit_limit'], 2); ?>
                                    </td>
                                    <td class="py-4 px-6 text-right font-bold <?php echo $c['outstanding_balance'] > 0 ? 'text-red-600' : 'text-green-600'; ?>">
                                        Ksh <?php echo number_format($c['outstanding_balance'], 2); ?>
                                    </td>
                                    <td class="py-4 px-6 flex items-center justify-center gap-2">
                                        <a href="customer_account.php?id=<?php echo $c['id']; ?>" class="bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition duration-150 font-bold flex items-center gap-1">
                                            <i class="fas fa-file-invoice-dollar"></i> View Ledger
                                        </a>
                                        <button onclick="triggerRepayModal(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['full_name']); ?>', <?php echo $c['outstanding_balance']; ?>)" 
                                                class="bg-green-100 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-600 hover:text-white transition duration-150 font-bold <?php echo $c['outstanding_balance'] <= 0 ? 'opacity-40 cursor-not-allowed' : ''; ?>"
                                                <?php echo $c['outstanding_balance'] <= 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-hand-holding-hand"></i> Repay
                                        </button>
                                        <?php if ($_SESSION['role'] === 'admin'): ?>
                                            <button onclick="triggerCreditLimitModal(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['full_name']); ?>', <?php echo $c['credit_limit']; ?>)" 
                                                    class="bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg hover:bg-blue-600 hover:text-white transition duration-150 font-bold">
                                                <i class="fas fa-sliders"></i> Limit
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL: REGISTER WHOLESALE BUYER -->
    <div id="modal-register" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition duration-300">
            <div class="bg-blue-800 text-white p-5 flex justify-between items-center">
                <h3 class="font-bold text-lg"><i class="fas fa-user-plus mr-2"></i> Register Wholesale Buyer</h3>
                <button onclick="toggleRegisterModal(false)" class="text-white/80 hover:text-white"><i class="fas fa-times-circle text-xl"></i></button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="register_wholesale">
                <?php echo csrf_field(); ?>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Company / Customer Name *</label>
                    <input type="text" name="full_name" required placeholder="e.g. Kisumu Fish Distributors Ltd"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Phone Number *</label>
                        <input type="text" name="phone" required placeholder="e.g. 0712345678"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Credit Limit (Ksh)</label>
                        <input type="number" name="credit_limit" value="50000" min="0" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" name="email" placeholder="e.g. info@buyer.com (Optional)"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Physical Business Address</label>
                    <input type="text" name="address" placeholder="e.g. Kibuye Market Stall 45B, Kisumu"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="toggleRegisterModal(false)" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 rounded-xl font-bold transition duration-150">Cancel</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold transition duration-150">Register Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADJUST CREDIT LIMIT -->
    <div id="modal-limit" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="bg-blue-800 text-white p-5 flex justify-between items-center">
                <h3 class="font-bold text-base"><i class="fas fa-sliders mr-2"></i> Adjust Credit Limit</h3>
                <button onclick="toggleCreditLimitModal(false)" class="text-white/80 hover:text-white"><i class="fas fa-times-circle text-lg"></i></button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <p class="font-bold text-slate-700 text-sm" id="limit-cust-name"></p>
                <input type="hidden" id="limit-cust-id">
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">New Credit Limit (Ksh)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3 text-slate-400 font-bold">Ksh</span>
                        <input type="number" id="limit-amount" min="0"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-4 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button onclick="toggleCreditLimitModal(false)" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-semibold">Cancel</button>
                    <button onclick="submitCreditLimit()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-semibold">Save Limit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: DEBT REPAYMENT -->
    <div id="modal-repay" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="bg-green-800 text-white p-5 flex justify-between items-center">
                <h3 class="font-bold text-base"><i class="fas fa-hand-holding-dollar mr-2"></i> Process Debt Repayment</h3>
                <button onclick="toggleRepayModal(false)" class="text-white/80 hover:text-white"><i class="fas fa-times-circle text-lg"></i></button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <input type="hidden" id="repay-cust-id">
                <div>
                    <p class="font-semibold text-slate-500">Customer Account:</p>
                    <p class="font-bold text-slate-800 text-sm mt-0.5" id="repay-cust-name"></p>
                </div>
                <div>
                    <p class="font-semibold text-slate-500">Current Outstanding Balance:</p>
                    <p class="font-bold text-red-600 text-base mt-0.5" id="repay-cust-debt">Ksh 0.00</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Repayment Amount (Ksh)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3 text-slate-400 font-bold">Ksh</span>
                        <input type="number" id="repay-amount" min="0.01" step="0.01"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-4 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Payment Method</label>
                    <select id="repay-method" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="cash">Cash Deposit</option>
                        <option value="mpesa">M-Pesa / Bank Transfer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Transaction Notes / Reference</label>
                    <input type="text" id="repay-notes" placeholder="e.g. M-Pesa ref or Cheque number"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="pt-4 flex gap-3">
                    <button onclick="toggleRepayModal(false)" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-semibold">Cancel</button>
                    <button onclick="submitRepayment()" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl font-semibold">Verify & Post</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-side filter and Modals Logic -->
    <script>
        // Inline Search Filter
        document.getElementById('ledger-search').addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('#customers-table tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(1)').innerText.toLowerCase();
                const phone = row.querySelector('td:nth-child(2)').innerText.toLowerCase();
                if (name.includes(term) || phone.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Modals control
        function toggleRegisterModal(show) {
            document.getElementById('modal-register').classList.toggle('hidden', !show);
        }

        function toggleCreditLimitModal(show) {
            document.getElementById('modal-limit').classList.toggle('hidden', !show);
        }

        function toggleRepayModal(show) {
            document.getElementById('modal-repay').classList.toggle('hidden', !show);
        }

        // Trigger limit adjustment
        function triggerCreditLimitModal(id, name, limit) {
            document.getElementById('limit-cust-id').value = id;
            document.getElementById('limit-cust-name').innerText = name;
            document.getElementById('limit-amount').value = parseFloat(limit);
            toggleCreditLimitModal(true);
        }

        // Trigger Debt payment
        function triggerRepayModal(id, name, balance) {
            document.getElementById('repay-cust-id').value = id;
            document.getElementById('repay-cust-name').innerText = name;
            document.getElementById('repay-cust-debt').innerText = `Ksh ${parseFloat(balance).toFixed(2)}`;
            document.getElementById('repay-amount').value = parseFloat(balance).toFixed(2);
            document.getElementById('repay-notes').value = '';
            toggleRepayModal(true);
        }

        // Submit Credit Limit adjustment
        async function submitCreditLimit() {
            const id = document.getElementById('limit-cust-id').value;
            const amount = parseFloat(document.getElementById('limit-amount').value) || 0;

            try {
                const response = await fetch(`${window.baseUrl}/controllers/DebtController.php?action=update_credit_limit`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        customer_id: id,
                        credit_limit: amount
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Credit limit updated successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred.');
            }
        }

        // Submit repayment
        async function submitRepayment() {
            const id = document.getElementById('repay-cust-id').value;
            const amount = parseFloat(document.getElementById('repay-amount').value) || 0;
            const method = document.getElementById('repay-method').value;
            const notes = document.getElementById('repay-notes').value.trim();

            if (amount <= 0) {
                alert('Please enter a valid repayment amount greater than 0.');
                return;
            }

            try {
                const response = await fetch(`${window.baseUrl}/controllers/DebtController.php?action=record_payment`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        customer_id: id,
                        amount: amount,
                        payment_method: method,
                        notes: notes
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Repayment processed and posted successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred.');
            }
        }
    </script>
</body>
</html>
