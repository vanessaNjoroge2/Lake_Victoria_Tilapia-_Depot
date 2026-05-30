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

    // Fetch active Raw fish list for starting a batch
    $rawQ = "SELECT id, name, size, stock_qty, unit FROM fish WHERE type = 'raw' AND is_active = 1 ORDER BY name ASC, size ASC";
    $rawS = $db->prepare($rawQ);
    $rawS->execute();
    $rawFishList = $rawS->fetchAll(PDO::FETCH_ASSOC);

    // Fetch dashboard stats
    $statsQ = "SELECT 
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as active_cnt,
                SUM(CASE WHEN status = 'completed' THEN fried_quantity_actual ELSE 0 END) as tot_fried,
                AVG(CASE WHEN status = 'completed' THEN total_frying_cost ELSE NULL END) as avg_cost
               FROM frying_batches";
    $statsS = $db->prepare($statsQ);
    $statsS->execute();
    $stats = $statsS->fetch(PDO::FETCH_ASSOC);

    // Fetch all batches
    $listQ = "SELECT b.*, 
                     r.name as raw_name, r.size as raw_size,
                     f.name as fried_name, f.size as fried_size,
                     u.full_name as started_by_name
              FROM frying_batches b
              JOIN fish r ON b.raw_fish_id = r.id
              JOIN fish f ON b.fried_fish_id = f.id
              JOIN users u ON b.started_by = u.id
              ORDER BY b.started_at DESC";
    $listS = $db->prepare($listQ);
    $listS->execute();
    $batches = $listS->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("<div class='p-8 text-center text-red-600 font-bold'>" . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frying Batches - <?php echo SITE_NAME; ?></title>
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
                <h1 class="text-3xl font-bold text-gray-800 font-sans">Fish Processing & Frying</h1>
                <p class="text-gray-600">Track raw fish frying batches, oil/fuel overheads, and automatic stock conversions</p>
            </div>
            
            <button onclick="toggleStartModal(true)" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 shadow-md transition duration-150 flex items-center">
                <i class="fas fa-fire-burner mr-2"></i> Start Frying Batch
            </button>
        </div>

        <!-- Frying KPI Stats Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-amber-100 text-amber-600 rounded-xl mr-4">
                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Active Batches</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo (int)($stats['active_cnt'] ?? 0); ?> frying</h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-green-100 text-green-600 rounded-xl mr-4">
                    <i class="fas fa-bowl-food text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Fried Stock Added</span>
                    <h3 class="text-2xl font-bold text-green-600 mt-1"><?php echo number_format((float)($stats['tot_fried'] ?? 0)); ?> pieces</h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-blue-100 text-blue-600 rounded-xl mr-4">
                    <i class="fas fa-calculator text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Avg Batch Overhead Cost</span>
                    <h3 class="text-2xl font-bold text-blue-600 mt-1">Ksh <?php echo number_format((float)($stats['avg_cost'] ?? 0.0), 2); ?></h3>
                </div>
            </div>
        </div>

        <!-- Frying Batches Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-base"><i class="fas fa-list-check mr-2 text-slate-500"></i> Frying Audit Ledger</h3>
                <span class="text-xs text-slate-500 font-semibold">Ordered: Newest First</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-6">Batch Ref / Date</th>
                            <th class="py-3.5 px-6">Product Details</th>
                            <th class="py-3.5 px-6 text-center">Quantities</th>
                            <th class="py-3.5 px-6 text-right">Frying Overheads</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($batches)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-fire-burner text-4xl mb-3"></i>
                                    <p class="text-sm">No frying batch records logged yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($batches as $b): ?>
                                <tr class="hover:bg-slate-50/50 transition duration-100">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-800"><?php echo $b['batch_ref']; ?></p>
                                        <p class="text-[10px] text-slate-400 mt-0.5"><?php echo date('d-M-Y H:i', strtotime($b['started_at'])); ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-700">Raw: <?php echo htmlspecialchars($b['raw_name']); ?> (<?php echo htmlspecialchars($b['raw_size']); ?>)</p>
                                        <p class="text-[10px] text-slate-500">Fried: <?php echo htmlspecialchars($b['fried_name']); ?> (<?php echo htmlspecialchars($b['fried_size']); ?>)</p>
                                        <p class="text-[9px] text-slate-400 mt-1">Cashier: <?php echo htmlspecialchars($b['started_by_name']); ?></p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <p><span class="font-semibold text-slate-500">Raw Input:</span> <span class="font-bold text-slate-700"><?php echo $b['raw_quantity']; ?></span></p>
                                        <?php if ($b['status'] === 'completed'): ?>
                                            <p><span class="font-semibold text-slate-500">Actual Fried:</span> <span class="font-bold text-green-600"><?php echo $b['fried_quantity_actual']; ?></span></p>
                                        <?php else: ?>
                                            <p><span class="font-semibold text-slate-500">Expected:</span> <span class="font-bold text-slate-600"><?php echo $b['fried_quantity_expected']; ?></span></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <?php if ($b['status'] === 'completed'): ?>
                                            <p class="font-bold text-slate-800">Ksh <?php echo number_format($b['total_frying_cost'], 2); ?></p>
                                            <p class="text-[9px] text-slate-400 mt-0.5">oil:<?php echo $b['oil_cost']; ?> | fuel:<?php echo $b['fuel_cost']; ?> | labor:<?php echo $b['labor_cost']; ?></p>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">Pending completion</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                            <?php echo match ($b['status']) {
                                                'in_progress' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                                'completed' => 'bg-green-100 text-green-800 border border-green-200',
                                                'cancelled' => 'bg-red-100 text-red-800 border border-red-200',
                                                default => 'bg-gray-100 text-gray-800'
                                            }; ?>">
                                            <?php echo str_replace('_', ' ', $b['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 flex items-center justify-center gap-1.5">
                                        <?php if ($b['status'] === 'in_progress'): ?>
                                            <button onclick="triggerCompleteModal(<?php echo $b['id']; ?>, '<?php echo $b['batch_ref']; ?>', <?php echo $b['fried_quantity_expected']; ?>, <?php echo (float)($b['oil_cost'] ?? 0); ?>, <?php echo (float)($b['fuel_cost'] ?? 0); ?>, <?php echo (float)($b['labor_cost'] ?? 0); ?>)"
                                                    class="bg-green-600 text-white px-2.5 py-1.5 rounded-lg hover:bg-green-700 font-bold transition duration-150">
                                                <i class="fas fa-check-circle mr-1"></i> Complete
                                            </button>
                                            <button onclick="triggerCancelModal(<?php echo $b['id']; ?>, '<?php echo $b['batch_ref']; ?>')"
                                                    class="bg-red-100 text-red-700 px-2.5 py-1.5 rounded-lg hover:bg-red-600 hover:text-white font-bold transition duration-150">
                                                <i class="fas fa-ban mr-1"></i> Cancel
                                            </button>
                                        <?php else: ?>
                                            <?php if (!empty($b['notes'])): ?>
                                                <button onclick="alert('Batch notes:\n<?php echo htmlspecialchars(str_replace(["\r", "\n"], ' ', $b['notes'])); ?>')" class="text-slate-400 hover:text-slate-600 p-1.5" title="View Notes">
                                                    <i class="fas fa-circle-info text-base"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-slate-300">-</span>
                                            <?php endif; ?>
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

    <!-- MODAL: START FRYING BATCH -->
    <div id="modal-start" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="bg-blue-800 text-white p-5 flex justify-between items-center">
                <h3 class="font-bold text-lg"><i class="fas fa-fire-burner mr-2"></i> Start Frying Batch</h3>
                <button onclick="toggleStartModal(false)" class="text-white/80 hover:text-white"><i class="fas fa-times-circle text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Select Raw Fish Product *</label>
                    <select id="start-raw-id" onchange="updateStartStockDisplay()"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Choose Raw Product Size --</option>
                        <?php foreach ($rawFishList as $raw): ?>
                            <option value="<?php echo $raw['id']; ?>" data-stock="<?php echo $raw['stock_qty']; ?>" data-unit="<?php echo $raw['unit']; ?>">
                                <?php echo htmlspecialchars($raw['name']); ?> (<?php echo htmlspecialchars($raw['size']); ?>) - Available: <?php echo $raw['stock_qty']; ?> <?php echo $raw['unit']; ?>s
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Raw Qty to Fry *</label>
                        <input type="number" id="start-raw-qty" min="1" placeholder="e.g. 20" oninput="syncStartExpectedQty()"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Expected Fried Quantity</label>
                        <input type="number" id="start-expected-qty" min="1" placeholder="e.g. 20"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Est. Oil Cost (Ksh)</label>
                        <input type="number" id="start-oil-cost" value="0" min="0"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Est. Fuel Cost (Ksh)</label>
                        <input type="number" id="start-fuel-cost" value="0" min="0"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Est. Labor Cost (Ksh)</label>
                        <input type="number" id="start-labor-cost" value="0" min="0"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Processing Notes</label>
                    <input type="text" id="start-notes" placeholder="e.g. Frying using oil container #3"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="pt-4 flex gap-3">
                    <button onclick="toggleStartModal(false)" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 rounded-xl font-bold">Cancel</button>
                    <button onclick="submitStartBatch()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold">Start Frying</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: COMPLETE BATCH -->
    <div id="modal-complete" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="bg-green-800 text-white p-5 flex justify-between items-center">
                <h3 class="font-bold text-base"><i class="fas fa-check-circle mr-2"></i> Complete Frying Batch</h3>
                <button onclick="toggleCompleteModal(false)" class="text-white/80 hover:text-white"><i class="fas fa-times-circle text-lg"></i></button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <input type="hidden" id="complete-batch-id">
                
                <div>
                    <p class="font-semibold text-slate-500">Frying Batch Reference:</p>
                    <p class="font-bold text-slate-800 text-sm mt-0.5" id="complete-batch-ref"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Actual Fried Quantity Obtained *</label>
                    <input type="number" id="complete-actual-qty" min="0" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Oil Cost (Ksh)</label>
                        <input type="number" id="complete-oil-cost" value="0" min="0"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Fuel Cost (Ksh)</label>
                        <input type="number" id="complete-fuel-cost" value="0" min="0"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Labor Cost (Ksh)</label>
                        <input type="number" id="complete-labor-cost" value="0" min="0"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Batch Completion Notes</label>
                    <input type="text" id="complete-notes" placeholder="e.g. Good quality, no wastage reported."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="pt-4 flex gap-3">
                    <button onclick="toggleCompleteModal(false)" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-semibold">Cancel</button>
                    <button onclick="submitCompleteBatch()" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl font-semibold">Complete Batch</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: CANCEL BATCH -->
    <div id="modal-cancel" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="bg-red-800 text-white p-5 flex justify-between items-center">
                <h3 class="font-bold text-base"><i class="fas fa-ban mr-2"></i> Cancel Frying Batch</h3>
                <button onclick="toggleCancelModal(false)" class="text-white/80 hover:text-white"><i class="fas fa-times-circle text-lg"></i></button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <input type="hidden" id="cancel-batch-id">
                
                <div>
                    <p class="font-semibold text-slate-500">Cancel Frying Batch:</p>
                    <p class="font-bold text-slate-800 text-sm mt-0.5" id="cancel-batch-ref"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Reason for Cancellation *</label>
                    <input type="text" id="cancel-reason" required placeholder="e.g. Wrong sizing selected"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="pt-4 flex gap-3">
                    <button onclick="toggleCancelModal(false)" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-semibold">Cancel</button>
                    <button onclick="submitCancelBatch()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl font-semibold">Confirm Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Logic -->
    <script>
        window.baseUrl = "<?php echo BASE_URL; ?>";

        // Modals control
        function toggleStartModal(show) {
            document.getElementById('modal-start').classList.toggle('hidden', !show);
            if (show) {
                document.getElementById('start-raw-id').value = '';
                document.getElementById('start-raw-qty').value = '';
                document.getElementById('start-expected-qty').value = '';
                document.getElementById('start-oil-cost').value = '0';
                document.getElementById('start-fuel-cost').value = '0';
                document.getElementById('start-labor-cost').value = '0';
                document.getElementById('start-notes').value = '';
            }
        }

        function toggleCompleteModal(show) {
            document.getElementById('modal-complete').classList.toggle('hidden', !show);
        }

        function toggleCancelModal(show) {
            document.getElementById('modal-cancel').classList.toggle('hidden', !show);
        }

        // Dropdown interactive helpers
        function updateStartStockDisplay() {
            syncStartExpectedQty();
        }

        // Sync raw qty to expected fried qty by default
        function syncStartExpectedQty() {
            const qty = document.getElementById('start-raw-qty').value;
            document.getElementById('start-expected-qty').value = qty;
        }

        // Start Submit
        async function submitStartBatch() {
            const selectEl = document.getElementById('start-raw-id');
            const rawFishId = selectEl.value;
            const rawQty = parseInt(document.getElementById('start-raw-qty').value) || 0;
            const expectedQty = parseInt(document.getElementById('start-expected-qty').value) || 0;
            const oil = parseFloat(document.getElementById('start-oil-cost').value) || 0;
            const fuel = parseFloat(document.getElementById('start-fuel-cost').value) || 0;
            const labor = parseFloat(document.getElementById('start-labor-cost').value) || 0;
            const notes = document.getElementById('start-notes').value.trim();

            if (!rawFishId) {
                alert('Please select a raw fish product.');
                return;
            }

            if (rawQty <= 0) {
                alert('Please enter a positive raw fish quantity to fry.');
                return;
            }

            // Client-side stock check
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const stock = parseInt(selectedOpt.getAttribute('data-stock')) || 0;
            if (rawQty > stock) {
                alert(`Insufficient stock!\nRequested: ${rawQty}\nAvailable in inventory: ${stock}`);
                return;
            }

            try {
                const response = await fetch(`${window.baseUrl}/controllers/FryingController.php?action=start_batch`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        raw_fish_id: rawFishId,
                        raw_quantity: rawQty,
                        expected_quantity: expectedQty,
                        oil_cost: oil,
                        fuel_cost: fuel,
                        labor_cost: labor,
                        notes: notes
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Frying batch started successfully!');
                    window.location.reload();
                } else {
                    alert('Error starting batch: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred.');
            }
        }

        // Trigger Complete Modal
        function triggerCompleteModal(id, ref, expected, oil = 0, fuel = 0, labor = 0) {
            document.getElementById('complete-batch-id').value = id;
            document.getElementById('complete-batch-ref').innerText = ref;
            document.getElementById('complete-actual-qty').value = expected;
            document.getElementById('complete-oil-cost').value = oil;
            document.getElementById('complete-fuel-cost').value = fuel;
            document.getElementById('complete-labor-cost').value = labor;
            document.getElementById('complete-notes').value = '';
            toggleCompleteModal(true);
        }

        // Complete Submit
        async function submitCompleteBatch() {
            const id = document.getElementById('complete-batch-id').value;
            const actualQty = parseInt(document.getElementById('complete-actual-qty').value);
            const oil = parseFloat(document.getElementById('complete-oil-cost').value) || 0;
            const fuel = parseFloat(document.getElementById('complete-fuel-cost').value) || 0;
            const labor = parseFloat(document.getElementById('complete-labor-cost').value) || 0;
            const notes = document.getElementById('complete-notes').value.trim();

            if (isNaN(actualQty) || actualQty < 0) {
                alert('Please enter a valid actual fried quantity (equal or greater than 0).');
                return;
            }

            try {
                const response = await fetch(`${window.baseUrl}/controllers/FryingController.php?action=complete_batch`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        batch_id: id,
                        actual_quantity: actualQty,
                        oil_cost: oil,
                        fuel_cost: fuel,
                        labor_cost: labor,
                        notes: notes
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Frying batch completed and closed!');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred.');
            }
        }

        // Trigger Cancel Modal
        function triggerCancelModal(id, ref) {
            document.getElementById('cancel-batch-id').value = id;
            document.getElementById('cancel-batch-ref').innerText = ref;
            document.getElementById('cancel-reason').value = '';
            toggleCancelModal(true);
        }

        // Cancel Submit
        async function submitCancelBatch() {
            const id = document.getElementById('cancel-batch-id').value;
            const reason = document.getElementById('cancel-reason').value.trim();

            if (!reason) {
                alert('Please enter a cancellation reason.');
                return;
            }

            try {
                const response = await fetch(`${window.baseUrl}/controllers/FryingController.php?action=cancel_batch`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        batch_id: id,
                        reason: reason
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Batch cancelled. Raw stock returned to inventory!');
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
