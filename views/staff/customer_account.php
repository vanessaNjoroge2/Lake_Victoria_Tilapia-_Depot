<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../includes/sanitize.php';
require_once __DIR__ . '/../../includes/csrf.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$customerId = isset($_GET['id']) ? sanitize_int($_GET['id']) : 0;

if ($customerId <= 0) {
    header('Location: wholesale_customers.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch customer details
    $q = "SELECT * FROM users WHERE id = :id AND role = 'customer' AND customer_type = 'wholesale'";
    $s = $db->prepare($q);
    $s->execute([':id' => $customerId]);
    $customer = $s->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        throw new Exception("Wholesale customer account not found.");
    }

    // Fetch ledger statement timeline
    $ledgerQ = "SELECT l.*, s.sale_ref, u.full_name as recorder_name 
                FROM debt_ledger l 
                LEFT JOIN pos_sales s ON l.sale_id = s.id 
                LEFT JOIN users u ON l.recorded_by = u.id 
                WHERE l.customer_id = :cust_id 
                ORDER BY l.created_at ASC, l.id ASC";
    $ledgerS = $db->prepare($ledgerQ);
    $ledgerS->execute([':cust_id' => $customerId]);
    $entries = $ledgerS->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("<div class='p-8 text-center text-red-600 font-bold'>" . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger Statement - <?php echo htmlspecialchars($customer['full_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        @media print {
            .no-print { display: none !important; }
            .print-container { margin-left: 0 !important; width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar Layout -->
    <div class="no-print">
        <?php include '../includes/staff_sidebar.php'; ?>
    </div>

    <!-- Main Workspace Container -->
    <div class="flex-1 ml-64 p-8 print-container">
        
        <!-- Header Toolbar -->
        <div class="flex justify-between items-center mb-8 no-print">
            <div class="flex items-center gap-3">
                <a href="wholesale_customers.php" class="bg-slate-200 text-slate-700 p-2.5 rounded-xl hover:bg-slate-300 transition duration-150">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Ledger Statement Statement</h1>
                    <p class="text-sm text-gray-500">Statement for: <span class="font-bold text-slate-700"><?php echo htmlspecialchars($customer['full_name']); ?></span></p>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button onclick="window.print()" class="bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl font-bold hover:bg-slate-300 transition duration-150 flex items-center">
                    <i class="fas fa-print mr-2"></i> Print Statement
                </button>
                <button onclick="triggerRepayModal(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['full_name']); ?>', <?php echo $customer['outstanding_balance']; ?>)"
                        class="bg-green-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-green-700 shadow-md transition duration-150 flex items-center <?php echo $customer['outstanding_balance'] <= 0 ? 'opacity-40 cursor-not-allowed' : ''; ?>"
                        <?php echo $customer['outstanding_balance'] <= 0 ? 'disabled' : ''; ?>>
                    <i class="fas fa-hand-holding-dollar mr-2"></i> Post Repayment
                </button>
            </div>
        </div>

        <!-- Customer Summary Header Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Company / Client</span>
                <span class="font-bold text-slate-800 text-base block mt-1"><?php echo htmlspecialchars($customer['full_name']); ?></span>
                <span class="text-slate-500 text-xs mt-1 block"><i class="fas fa-location-dot mr-1"></i> <?php echo htmlspecialchars($customer['address'] ?? 'No address registered'); ?></span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Contact Information</span>
                <span class="text-slate-700 text-xs block mt-1"><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($customer['phone']); ?></span>
                <span class="text-slate-700 text-xs block mt-1"><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($customer['email'] ?? 'No email'); ?></span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Available Credit Limit</span>
                <span class="font-bold text-green-600 text-lg block mt-1">Ksh <?php echo number_format($customer['credit_limit'] - $customer['outstanding_balance'], 2); ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Approved Limit: Ksh <?php echo number_format($customer['credit_limit'], 2); ?></span>
            </div>
            <div class="bg-red-50 border border-red-200/50 rounded-2xl p-4 flex flex-col justify-center">
                <span class="text-xs text-red-600 font-bold uppercase tracking-wider block">Current Outstanding Debt</span>
                <span class="font-bold text-red-600 text-xl block mt-1">Ksh <?php echo number_format($customer['outstanding_balance'], 2); ?></span>
            </div>
        </div>

        <!-- Ledger Statement History Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-base"><i class="fas fa-file-lines mr-2 text-slate-500"></i> Statement Running Statement</h3>
                <span class="text-xs text-slate-500 font-semibold">Ordered: Chronological Sequence</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-6">Timestamp</th>
                            <th class="py-3 px-6">Event / Reference Description</th>
                            <th class="py-3 px-6 text-right">Debit (Debt Accrued)</th>
                            <th class="py-3 px-6 text-right">Credit (Repaid)</th>
                            <th class="py-3 px-6 text-right">Running Balance</th>
                            <th class="py-3 px-6 text-center no-print">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <!-- Starting Balance Entry -->
                        <tr class="bg-slate-50/50 font-medium">
                            <td class="py-3 px-6 text-slate-400">
                                <?php echo date('d-M-Y H:i', strtotime($customer['created_at'])); ?>
                            </td>
                            <td class="py-3 px-6 text-slate-500 italic">
                                Account created / Initialized Balance
                            </td>
                            <td class="py-3 px-6 text-right text-slate-400">-</td>
                            <td class="py-3 px-6 text-right text-slate-400">-</td>
                            <td class="py-3 px-6 text-right font-bold text-slate-700">Ksh 0.00</td>
                            <td class="py-3 px-6 text-center no-print">-</td>
                        </tr>

                        <?php if (empty($entries)): ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 font-medium">
                                    No ledger events recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($entries as $item): ?>
                                <tr class="hover:bg-slate-50/50 transition duration-100">
                                    <td class="py-3.5 px-6 text-slate-500">
                                        <?php echo date('d-M-Y H:i', strtotime($item['created_at'])); ?>
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <p class="font-bold text-slate-700">
                                            <?php if ($item['type'] === 'debt'): ?>
                                                <span class="text-red-600 mr-1.5"><i class="fas fa-circle-arrow-up"></i> Credit Sale</span>
                                                <?php echo $item['sale_ref'] ? "Invoice " . htmlspecialchars($item['sale_ref']) : ''; ?>
                                            <?php else: ?>
                                                <span class="text-green-600 mr-1.5"><i class="fas fa-circle-arrow-down"></i> Repayment Posted</span>
                                            <?php endif; ?>
                                        </p>
                                        <?php if (!empty($item['notes'])): ?>
                                            <p class="text-[10px] text-slate-400 mt-0.5 leading-relaxed"><?php echo htmlspecialchars($item['notes']); ?></p>
                                        <?php endif; ?>
                                        <p class="text-[9px] text-slate-400 mt-0.5">Recorded by: <?php echo htmlspecialchars($item['recorder_name'] ?? 'System'); ?></p>
                                    </td>
                                    <td class="py-3.5 px-6 text-right font-bold text-red-600">
                                        <?php echo $item['type'] === 'debt' ? 'Ksh ' . number_format($item['amount'], 2) : '-'; ?>
                                    </td>
                                    <td class="py-3.5 px-6 text-right font-bold text-green-600">
                                        <?php echo $item['type'] === 'payment' ? 'Ksh ' . number_format($item['amount'], 2) : '-'; ?>
                                    </td>
                                    <td class="py-3.5 px-6 text-right font-bold text-slate-800">
                                        Ksh <?php echo number_format($item['balance_after'], 2); ?>
                                    </td>
                                    <td class="py-3.5 px-6 text-center no-print">
                                        <?php if ($item['sale_id']): ?>
                                            <a href="pos_receipt.php?id=<?php echo $item['sale_id']; ?>" 
                                               class="bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 p-1.5 rounded-lg inline-flex items-center justify-center transition duration-150" 
                                               title="View Thermal Receipt">
                                                <i class="fas fa-receipt text-sm"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-300">-</span>
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

    <!-- MODAL: DEBT REPAYMENT -->
    <div id="modal-repay" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden no-print">
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

    <!-- Client Script -->
    <script>
        window.baseUrl = "<?php echo BASE_URL; ?>";

        function toggleRepayModal(show) {
            document.getElementById('modal-repay').classList.toggle('hidden', !show);
        }

        function triggerRepayModal(id, name, balance) {
            document.getElementById('repay-cust-id').value = id;
            document.getElementById('repay-cust-name').innerText = name;
            document.getElementById('repay-cust-debt').innerText = `Ksh ${parseFloat(balance).toFixed(2)}`;
            document.getElementById('repay-amount').value = parseFloat(balance).toFixed(2);
            document.getElementById('repay-notes').value = '';
            toggleRepayModal(true);
        }

        async function submitRepayment() {
            const id = document.getElementById('repay-cust-id').value;
            const amount = parseFloat(document.getElementById('repay-amount').value) || 0;
            const method = document.getElementById('repay-method').value;
            const notes = document.getElementById('repay-notes').value.trim();

            if (amount <= 0) {
                alert('Please enter a valid repayment amount.');
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
                    alert('Repayment processed successfully!');
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
