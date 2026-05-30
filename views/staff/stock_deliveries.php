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

    // Fetch active fish list for deliveries
    $fishQ = "SELECT id, name, size, type, stock_qty, unit, cost_price FROM fish WHERE is_active = 1 ORDER BY type ASC, name ASC, size ASC";
    $fishS = $db->prepare($fishQ);
    $fishS->execute();
    $fishList = $fishS->fetchAll(PDO::FETCH_ASSOC);

    // Fetch dashboard stats
    $statsQ = "SELECT 
                COUNT(*) as total_deliveries,
                SUM(total_cost) as total_spent,
                MAX(delivery_date) as last_delivery_date
               FROM stock_deliveries";
    $statsS = $db->prepare($statsQ);
    $statsS->execute();
    $stats = $statsS->fetch(PDO::FETCH_ASSOC);

    // Fetch deliveries list
    $listQ = "SELECT d.*, u.full_name as received_by_name 
              FROM stock_deliveries d
              JOIN users u ON d.received_by = u.id
              ORDER BY d.delivery_date DESC, d.created_at DESC";
    $listS = $db->prepare($listQ);
    $listS->execute();
    $deliveries = $listS->fetchAll(PDO::FETCH_ASSOC);

    // Load items for each delivery
    foreach ($deliveries as &$delivery) {
        $itemsQ = "SELECT di.*, f.name as fish_name, f.size, f.type, f.unit
                   FROM stock_delivery_items di
                   JOIN fish f ON di.fish_id = f.id
                   WHERE di.delivery_id = :id";
        $itemsS = $db->prepare($itemsQ);
        $itemsS->execute([':id' => $delivery['id']]);
        $delivery['items'] = $itemsS->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    die("<div class='p-8 text-center text-red-600 font-bold'>" . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Deliveries - <?php echo SITE_NAME; ?></title>
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
                <h1 class="text-3xl font-bold text-gray-800 font-sans">Stock Deliveries & Shipments</h1>
                <p class="text-gray-600">Log incoming fish shipments from suppliers, track wholesale costs, and replenish physical stock</p>
            </div>
            
            <button onclick="toggleRecordModal(true)" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 shadow-md transition duration-150 flex items-center">
                <i class="fas fa-truck-loading mr-2"></i> Record Delivery
            </button>
        </div>

        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-blue-100 text-blue-600 rounded-xl mr-4">
                    <i class="fas fa-file-invoice text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Shipments Logged</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo (int)($stats['total_deliveries'] ?? 0); ?> deliveries</h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-green-100 text-green-600 rounded-xl mr-4">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Acquisition Cost</span>
                    <h3 class="text-2xl font-bold text-green-600 mt-1">Ksh <?php echo number_format((float)($stats['total_spent'] ?? 0.0), 2); ?></h3>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center">
                <div class="p-4 bg-purple-100 text-purple-600 rounded-xl mr-4">
                    <i class="fas fa-calendar-check text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Latest Shipment Date</span>
                    <h3 class="text-2xl font-bold text-purple-600 mt-1">
                        <?php echo $stats['last_delivery_date'] ? date('d-M-Y', strtotime($stats['last_delivery_date'])) : 'None'; ?>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Deliveries Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-base"><i class="fas fa-history mr-2 text-slate-500"></i> Shipment Log Directory</h3>
                <span class="text-xs text-slate-500 font-semibold">Ordered: Newest First</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-6">Delivery Ref / Date</th>
                            <th class="py-3.5 px-6">Supplier Details</th>
                            <th class="py-3.5 px-6">Items Received</th>
                            <th class="py-3.5 px-6 text-right">Total Acquisition Cost</th>
                            <th class="py-3.5 px-6">Receiver Staff</th>
                            <th class="py-3.5 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($deliveries)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="fas fa-truck-loading text-4xl mb-3"></i>
                                    <p class="text-sm">No stock delivery shipments recorded yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deliveries as $d): ?>
                                <tr class="hover:bg-slate-50/50 transition duration-100 align-top">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-800"><?php echo htmlspecialchars($d['delivery_ref']); ?></p>
                                        <p class="text-[10px] font-semibold text-blue-600 mt-0.5">Ship: <?php echo date('d-M-Y', strtotime($d['delivery_date'])); ?></p>
                                        <p class="text-[9px] text-slate-400 mt-0.5">Logged: <?php echo date('d-M-Y H:i', strtotime($d['created_at'])); ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-700"><?php echo htmlspecialchars($d['supplier_name']); ?></p>
                                        <?php if (!empty($d['supplier_phone'])): ?>
                                            <p class="text-[10px] text-slate-500"><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($d['supplier_phone']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="space-y-1">
                                            <?php foreach ($d['items'] as $item): ?>
                                                <div class="flex items-center text-slate-700 bg-slate-50 px-2 py-1 rounded border border-slate-100">
                                                    <span class="font-bold mr-1 text-slate-800"><?php echo $item['quantity_received']; ?></span> 
                                                    <span>x <?php echo htmlspecialchars($item['fish_name']); ?> (<?php echo htmlspecialchars($item['size']); ?>, <?php echo htmlspecialchars($item['type']); ?>)</span>
                                                    <span class="ml-auto font-semibold text-[10px] text-slate-500">@ Ksh <?php echo number_format($item['cost_per_unit'], 2); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <p class="font-bold text-slate-800 text-sm">Ksh <?php echo number_format($d['total_cost'], 2); ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-medium text-slate-600"><?php echo htmlspecialchars($d['received_by_name']); ?></p>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if (!empty($d['notes'])): ?>
                                            <button onclick="alert('Delivery notes:\n<?php echo htmlspecialchars(str_replace(["\r", "\n"], ' ', $d['notes'])); ?>')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-lg font-bold transition duration-150">
                                                <i class="fas fa-sticky-note mr-1"></i> View Notes
                                            </button>
                                        <?php else: ?>
                                            <span class="text-slate-300 italic">No notes</span>
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

    <!-- MODAL: RECORD STOCK DELIVERY -->
    <div id="modal-record" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="bg-blue-800 text-white p-5 flex justify-between items-center flex-shrink-0">
                <h3 class="font-bold text-lg"><i class="fas fa-truck-loading mr-2"></i> Record Inbound Fish Shipment</h3>
                <button onclick="toggleRecordModal(false)" class="text-white/80 hover:text-white"><i class="fas fa-times-circle text-xl"></i></button>
            </div>
            
            <div class="p-6 overflow-y-auto space-y-4 text-xs flex-1">
                
                <!-- Supplier Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Supplier Name *</label>
                        <input type="text" id="supplier-name" required placeholder="e.g. Kisumu Fish Union"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Supplier Phone</label>
                        <input type="text" id="supplier-phone" placeholder="e.g. 0712345678"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Delivery Date *</label>
                        <input type="date" id="delivery-date" value="<?php echo date('Y-m-d'); ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Shipment items dynamic list -->
                <div class="border-t border-slate-100 pt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-bold text-slate-700 uppercase tracking-wider text-xs"><i class="fas fa-list mr-1"></i> Fish Shipment Item Breakdown</h4>
                        <button type="button" onclick="addShipmentItemRow()" class="bg-cyan-50 hover:bg-cyan-100 text-cyan-700 px-3 py-1.5 rounded-lg font-bold border border-cyan-200 flex items-center">
                            <i class="fas fa-plus mr-1"></i> Add Fish Item
                        </button>
                    </div>

                    <div id="shipment-items-container" class="space-y-3">
                        <!-- Dynamic item rows will be injected here -->
                    </div>
                </div>

                <!-- Notes & Totals -->
                <div class="border-t border-slate-100 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Shipment Notes / Gate Records</label>
                        <textarea id="delivery-notes" rows="2" placeholder="e.g. Shipment delivered by truck KBZ 123A, fish in good condition, chilled."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 flex flex-col justify-center">
                        <div class="flex justify-between items-center text-slate-500 font-semibold mb-1">
                            <span>Total Items:</span>
                            <span id="summary-total-qty">0 items</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-700">Total Purchase Cost:</span>
                            <span class="text-lg font-extrabold text-blue-700" id="summary-total-cost">Ksh 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="p-5 border-t border-slate-100 bg-slate-50 flex gap-3 flex-shrink-0">
                <button onclick="toggleRecordModal(false)" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 py-3 rounded-xl font-bold">Cancel</button>
                <button onclick="submitRecordDelivery()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-md shadow-blue-500/20">Record Shipment</button>
            </div>
        </div>
    </div>

    <!-- Scripts Logic -->
    <script>
        window.baseUrl = "<?php echo BASE_URL; ?>";
        const fishInventory = <?php echo json_encode($fishList); ?>;

        function toggleRecordModal(show) {
            document.getElementById('modal-record').classList.toggle('hidden', !show);
            if (show) {
                document.getElementById('supplier-name').value = '';
                document.getElementById('supplier-phone').value = '';
                document.getElementById('delivery-date').value = "<?php echo date('Y-m-d'); ?>";
                document.getElementById('delivery-notes').value = '';
                document.getElementById('shipment-items-container').innerHTML = '';
                
                // Add initial row automatically
                addShipmentItemRow();
            }
        }

        // Add a row to the dynamic table
        function addShipmentItemRow() {
            const container = document.getElementById('shipment-items-container');
            const rowIndex = container.children.length;

            const rowDiv = document.createElement('div');
            rowDiv.className = "flex flex-col md:flex-row gap-3 bg-slate-50/50 p-3 rounded-xl border border-slate-100 items-end relative";
            rowDiv.id = `shipment-row-${rowIndex}`;

            // Generate options HTML
            let optionsHtml = '<option value="">-- Select Fish Product --</option>';
            fishInventory.forEach(fish => {
                optionsHtml += `<option value="${fish.id}" data-cost="${fish.cost_price}" data-unit="${fish.unit}">
                    ${fish.name} (${fish.size}, ${fish.type}) [Stock: ${fish.stock_qty} ${fish.unit}]
                </option>`;
            });

            rowDiv.innerHTML = `
                <div class="flex-1 w-full">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fish Product Size/Type *</label>
                    <select class="item-fish-id w-full bg-white border border-slate-200 rounded-lg px-2.5 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            onchange="onProductSelectChange(${rowIndex})">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="w-full md:w-32">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Qty Received *</label>
                    <input type="number" class="item-qty w-full bg-white border border-slate-200 rounded-lg px-2.5 py-2 font-semibold text-center focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="e.g. 50" min="1" oninput="calculateRowTotals()">
                </div>
                <div class="w-full md:w-36">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Cost Per Unit (Ksh) *</label>
                    <input type="number" step="0.01" class="item-cost w-full bg-white border border-slate-200 rounded-lg px-2.5 py-2 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="e.g. 250" min="0" oninput="calculateRowTotals()">
                </div>
                <div class="w-full md:w-32 flex flex-col justify-end">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Line Total</span>
                    <span class="item-line-total font-bold text-slate-700 py-2 block text-right pr-2">Ksh 0.00</span>
                </div>
                <button type="button" onclick="removeShipmentItemRow(${rowIndex})" 
                        class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-lg border border-red-100 mb-0.5" title="Remove Row">
                    <i class="fas fa-trash-alt"></i>
                </button>
            `;

            container.appendChild(rowDiv);
            calculateRowTotals();
        }

        // Remove row
        function removeShipmentItemRow(index) {
            const row = document.getElementById(`shipment-row-${index}`);
            if (row) {
                row.remove();
            }
            calculateRowTotals();
        }

        // When product changes, prefill its last recorded cost_price
        function onProductSelectChange(index) {
            const row = document.getElementById(`shipment-row-${index}`);
            if (!row) return;

            const selectEl = row.querySelector('.item-fish-id');
            const costInput = row.querySelector('.item-cost');

            if (selectEl.selectedIndex > 0) {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const cost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
                costInput.value = cost;
            }
            calculateRowTotals();
        }

        // Calculate live totals on screen
        function calculateRowTotals() {
            const container = document.getElementById('shipment-items-container');
            let totalCost = 0.0;
            let totalQty = 0;

            Array.from(container.children).forEach(row => {
                const selectEl = row.querySelector('.item-fish-id');
                const qtyInput = row.querySelector('.item-qty');
                const costInput = row.querySelector('.item-cost');
                const lineTotalSpan = row.querySelector('.item-line-total');

                const qty = parseInt(qtyInput.value) || 0;
                const cost = parseFloat(costInput.value) || 0.0;

                const lineTotal = qty * cost;
                totalCost += lineTotal;
                totalQty += qty;

                lineTotalSpan.innerText = `Ksh ${lineTotal.toFixed(2)}`;
            });

            document.getElementById('summary-total-qty').innerText = `${totalQty} pieces/items`;
            document.getElementById('summary-total-cost').innerText = `Ksh ${totalCost.toFixed(2)}`;
        }

        // Submit form via fetch
        async function submitRecordDelivery() {
            const supplierName = document.getElementById('supplier-name').value.trim();
            const supplierPhone = document.getElementById('supplier-phone').value.trim();
            const deliveryDate = document.getElementById('delivery-date').value;
            const notes = document.getElementById('delivery-notes').value.trim();
            
            if (!supplierName) {
                alert('Supplier Name is required.');
                return;
            }

            const container = document.getElementById('shipment-items-container');
            if (container.children.length === 0) {
                alert('Please add at least one fish item.');
                return;
            }

            const items = [];
            let validationError = null;

            Array.from(container.children).forEach((row, index) => {
                const selectEl = row.querySelector('.item-fish-id');
                const qtyInput = row.querySelector('.item-qty');
                const costInput = row.querySelector('.item-cost');

                const fishId = parseInt(selectEl.value);
                const qty = parseInt(qtyInput.value);
                const cost = parseFloat(costInput.value);

                if (!fishId) {
                    validationError = `Row ${index + 1}: Select a fish product.`;
                } else if (isNaN(qty) || qty <= 0) {
                    validationError = `Row ${index + 1}: Enter a positive quantity received.`;
                } else if (isNaN(cost) || cost < 0) {
                    validationError = `Row ${index + 1}: Enter a valid purchase cost per unit.`;
                }

                if (!validationError) {
                    items.push({
                        fish_id: fishId,
                        quantity_received: qty,
                        cost_per_unit: cost
                    });
                }
            });

            if (validationError) {
                alert(validationError);
                return;
            }

            try {
                const response = await fetch(`${window.baseUrl}/controllers/StockController.php?action=record_delivery`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        supplier_name: supplierName,
                        supplier_phone: supplierPhone,
                        delivery_date: deliveryDate,
                        notes: notes,
                        items: items
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error recording shipment: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Connection error occurred.');
            }
        }
    </script>
</body>
</html>
