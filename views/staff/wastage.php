<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/sanitize.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch active fish list for selection
    $fishQ = "SELECT id, name, size, type, stock_qty, unit, cost_price, retail_price FROM fish WHERE is_active = 1 ORDER BY type ASC, name ASC, size ASC";
    $fishS = $db->prepare($fishQ);
    $fishS->execute();
    $fishList = $fishS->fetchAll(PDO::FETCH_ASSOC);

    // Fetch dashboard stats
    $statsQ = "SELECT 
                COUNT(*) as total_records,
                SUM(quantity) as total_units_wasted,
                SUM(estimated_loss) as total_financial_loss
               FROM wastage_log";
    $statsS = $db->prepare($statsQ);
    $statsS->execute();
    $stats = $statsS->fetch(PDO::FETCH_ASSOC);

    // Fetch wastage log
    $listQ = "SELECT w.*, f.name as fish_name, f.size, f.type as fish_type, f.unit, u.full_name as recorded_by_name 
              FROM wastage_log w
              JOIN fish f ON w.fish_id = f.id
              JOIN users u ON w.recorded_by = u.id
              ORDER BY w.created_at DESC";
    $listS = $db->prepare($listQ);
    $listS->execute();
    $wastageLogs = $listS->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("<div class='p-8 text-center text-red-600 font-bold'>" . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wastage Logs - <?php echo SITE_NAME; ?></title>
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

    <!-- Main Workspace Container -->
    <div class="flex-1 ml-64 p-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 font-sans">Wastage & Spoilage Audits</h1>
                <p class="text-gray-600">Track expired, spoiled, or damaged raw and fried fish stock with documented loss evaluations</p>
            </div>
            
            <button onclick="toggleWastageModal(true)" class="bg-red-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-red-700 shadow-md transition duration-150 flex items-center">
                <i class="fas fa-trash-alt mr-2"></i> Log Spoiled Fish
            </button>
        </div>

        <!-- Wastage KPI Stats Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-red-100 text-red-600 rounded-xl mr-4">
                    <i class="fas fa-dumpster text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Wastage Instances</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo (int)($stats['total_records'] ?? 0); ?> incidents</h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-amber-100 text-amber-600 rounded-xl mr-4">
                    <i class="fas fa-fish text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Fish Discarded</span>
                    <h3 class="text-2xl font-bold text-amber-600 mt-1"><?php echo number_format((float)($stats['total_units_wasted'] ?? 0)); ?> pieces</h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-red-100 text-red-700 rounded-xl mr-4">
                    <i class="fas fa-chart-bar text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Estimated Financial Loss</span>
                    <h3 class="text-2xl font-bold text-red-600 mt-1">Ksh <?php echo number_format((float)($stats['total_financial_loss'] ?? 0.0), 2); ?></h3>
                </div>
            </div>
        </div>

        <!-- Wastage Log Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-base"><i class="fas fa-list-check mr-2 text-slate-500"></i> Discard & Spoilage History</h3>
                <span class="text-xs text-slate-500 font-semibold">Ordered: Newest First</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-6">Incident ID / Date</th>
                            <th class="py-3.5 px-6">Fish Details</th>
                            <th class="py-3.5 px-6 text-center">Quantity Discarded</th>
                            <th class="py-3.5 px-6 text-right">Estimated Financial Loss</th>
                            <th class="py-3.5 px-6">Reason for Discard</th>
                            <th class="py-3.5 px-6">Logged By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($wastageLogs)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-trash-can text-4xl mb-3"></i>
                                    <p class="text-sm">No wastage logs recorded. Excellent inventory control!</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($wastageLogs as $log): ?>
                                <tr class="hover:bg-slate-50/50 transition duration-100">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-800">#WAST-<?php echo $log['id']; ?></p>
                                        <p class="text-[9px] text-slate-400 mt-0.5"><?php echo date('d-M-Y H:i', strtotime($log['created_at'])); ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-700"><?php echo htmlspecialchars($log['fish_name']); ?></p>
                                        <p class="text-[10px] text-slate-500">Size: <?php echo htmlspecialchars($log['size']); ?> | Classification: <span class="capitalize text-slate-600 font-semibold"><?php echo htmlspecialchars($log['fish_type']); ?></span></p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="font-bold text-slate-700 bg-red-50 text-red-800 border border-red-100 px-2.5 py-1 rounded-lg">
                                            <?php echo $log['quantity']; ?> <?php echo htmlspecialchars($log['unit']); ?>s
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-bold text-red-600">Ksh <?php echo number_format($log['estimated_loss'], 2); ?></p>
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-600">
                                        <p><?php echo htmlspecialchars($log['reason']); ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-medium text-slate-600"><?php echo htmlspecialchars($log['recorded_by_name']); ?></p>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL: RECORD STOCK WASTAGE -->
    <div id="modal-wastage" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="bg-red-800 text-white p-5 flex justify-between items-center">
                <h3 class="font-bold text-base"><i class="fas fa-trash-alt mr-2"></i> Log Spoilage / Wastage Incident</h3>
                <button onclick="toggleWastageModal(false)" class="text-white/80 hover:text-white"><i class="fas fa-times-circle text-lg"></i></button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Select Fish Product *</label>
                    <select id="waste-fish-id" onchange="updateWastageStockDisplay()"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Select Product Size & Type --</option>
                        <?php foreach ($fishList as $fish): ?>
                            <option value="<?php echo $fish['id']; ?>" data-stock="<?php echo $fish['stock_qty']; ?>" data-unit="<?php echo $fish['unit']; ?>">
                                <?php echo htmlspecialchars($fish['name']); ?> (<?php echo htmlspecialchars($fish['size']); ?>, <?php echo htmlspecialchars($fish['type']); ?>) - Available: <?php echo $fish['stock_qty']; ?> <?php echo $fish['unit']; ?>s
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Quantity Discarded *</label>
                        <input type="number" id="waste-quantity" min="1" placeholder="e.g. 5"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Discard Reason *</label>
                        <select id="waste-reason"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">-- Choose Reason --</option>
                            <option value="Spoiled (Off-smell / Rot)">Spoiled (Off-smell / Rot)</option>
                            <option value="Physical Damage (Broken/Crushed)">Physical Damage (Broken/Crushed)</option>
                            <option value="Expired / Excess Holding Time">Expired / Excess Holding Time</option>
                            <option value="Frying Burnout / Undersold Batch">Frying Burnout / Undersold Batch</option>
                            <option value="Contamination / Hygiene Void">Contamination / Hygiene Void</option>
                            <option value="Other / Customer Return Void">Other / Customer Return Void</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button onclick="toggleWastageModal(false)" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 rounded-xl font-bold">Cancel</button>
                    <button onclick="submitRecordWastage()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-bold shadow-md shadow-red-500/20">Log Wastage</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Logic -->
    <script>
        window.baseUrl = "<?php echo BASE_URL; ?>";

        function toggleWastageModal(show) {
            document.getElementById('modal-wastage').classList.toggle('hidden', !show);
            if (show) {
                document.getElementById('waste-fish-id').value = '';
                document.getElementById('waste-quantity').value = '';
                document.getElementById('waste-reason').value = '';
            }
        }

        function updateWastageStockDisplay() {
            // Can be used to update placeholders or inline warnings
        }

        async function submitRecordWastage() {
            const selectEl = document.getElementById('waste-fish-id');
            const fishId = selectEl.value;
            const quantity = parseInt(document.getElementById('waste-quantity').value) || 0;
            const reason = document.getElementById('waste-reason').value.trim();

            if (!fishId) {
                alert('Please select a fish product.');
                return;
            }

            if (quantity <= 0) {
                alert('Please enter a positive discard quantity.');
                return;
            }

            if (!reason) {
                alert('Please select or specify a reason for discarding.');
                return;
            }

            // Client-side stock check
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const stock = parseInt(selectedOpt.getAttribute('data-stock')) || 0;
            if (quantity > stock) {
                alert(`Insufficient stock in inventory!\nRequested: ${quantity}\nAvailable to discard: ${stock}`);
                return;
            }

            try {
                const response = await fetch(`${window.baseUrl}/controllers/StockController.php?action=record_wastage`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        fish_id: fishId,
                        quantity: quantity,
                        reason: reason
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Wastage logged and stock adjusted successfully!');
                    window.location.reload();
                } else {
                    alert('Error logging wastage: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred.');
            }
        }
    </script>
</body>
</html>
