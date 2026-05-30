<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../includes/sanitize.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);

$sale_id = isset($_GET['id']) ? sanitize_int($_GET['id']) : 0;
$sale_ref = isset($_GET['ref']) ? trim($_GET['ref']) : '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch sale record
    if ($sale_id > 0) {
        $query = "SELECT s.*, u.full_name as cashier_name 
                  FROM pos_sales s 
                  JOIN users u ON s.cashier_id = u.id 
                  WHERE s.id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $sale_id]);
    } else if (!empty($sale_ref)) {
        $query = "SELECT s.*, u.full_name as cashier_name 
                  FROM pos_sales s 
                  JOIN users u ON s.cashier_id = u.id 
                  WHERE s.sale_ref = :ref";
        $stmt = $db->prepare($query);
        $stmt->execute([':ref' => $sale_ref]);
    } else {
        throw new Exception("Missing receipt identifier.");
    }

    $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sale) {
        throw new Exception("Transaction receipt not found.");
    }

    $sale_id = $sale['id'];

    // Fetch items
    $itemQuery = "SELECT * FROM pos_sale_items WHERE sale_id = :sale_id";
    $itemStmt = $db->prepare($itemQuery);
    $itemStmt->execute([':sale_id' => $sale_id]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("<div class='p-8 text-center text-red-600 font-bold'>" . htmlspecialchars($e->getMessage()) . "</div>");
}

// Generate WhatsApp text for digital sharing
$whatsappMsg = "*LAKE VICTORIA TILAPIA DEPOT*\n";
$whatsappMsg .= "Receipt: *" . $sale['sale_ref'] . "*\n";
$whatsappMsg .= "Date: " . date('d/m/Y H:i', strtotime($sale['created_at'])) . "\n";
$whatsappMsg .= "Cashier: " . $sale['cashier_name'] . "\n";
$whatsappMsg .= "-------------------------------\n";
foreach ($items as $item) {
    $whatsappMsg .= $item['fish_name'] . " (" . $item['size'] . ", " . ucfirst($item['type']) . ") x" . $item['quantity'] . " @ Ksh " . number_format($item['unit_price'], 2) . " = Ksh " . number_format($item['line_total'], 2) . "\n";
}
$whatsappMsg .= "-------------------------------\n";
$whatsappMsg .= "Subtotal: Ksh " . number_format($sale['subtotal'], 2) . "\n";
if ($sale['discount'] > 0) {
    $whatsappMsg .= "Discount: Ksh " . number_format($sale['discount'], 2) . "\n";
}
$whatsappMsg .= "*Total: Ksh " . number_format($sale['total'], 2) . "*\n";
$whatsappMsg .= "Payment: " . strtoupper($sale['payment_method']) . "\n";
if ($sale['payment_method'] === 'cash') {
    $whatsappMsg .= "Tendered: Ksh " . number_format($sale['amount_tendered'], 2) . "\n";
    $whatsappMsg .= "Change: Ksh " . number_format($sale['change_given'], 2) . "\n";
} else if ($sale['payment_method'] === 'mpesa') {
    $whatsappMsg .= "Mpesa Ref: " . $sale['mpesa_ref'] . "\n";
}
$whatsappMsg .= "-------------------------------\n";
$whatsappMsg .= "Thank you for shopping with us!\n";
$whatsappUrl = "https://api.whatsapp.com/send?phone=" . urlencode($sale['customer_phone'] ?? '') . "&text=" . urlencode($whatsappMsg);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo $sale['sale_ref']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                background-color: #fff;
                color: #000;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 80mm !important;
                max-width: 80mm !important;
            }
        }
        /* Styling for 80mm thermal roll view on screen */
        .thermal-receipt {
            width: 80mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            line-height: 1.4;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 flex flex-col items-center justify-center">

    <!-- Action Toolbar (No Print) -->
    <div class="no-print mb-6 flex flex-wrap gap-4 max-w-sm w-full justify-center">
        <a href="pos_sale.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition duration-150 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> POS Screen
        </a>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition duration-150 flex items-center">
            <i class="fas fa-print mr-2"></i> Print Receipt
        </button>
        <?php if (!empty($sale['customer_phone'])): ?>
            <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition duration-150 flex items-center">
                <i class="fab fa-whatsapp mr-2"></i> Share WhatsApp
            </a>
        <?php endif; ?>
    </div>

    <!-- 80mm Thermal Receipt Container -->
    <div class="print-container bg-white shadow-xl rounded-lg p-6 thermal-receipt border border-gray-200">
        <!-- Logo / Header -->
        <div class="text-center mb-4">
            <h1 class="font-bold text-lg uppercase tracking-wider">Lake Victoria</h1>
            <h2 class="font-bold text-base uppercase tracking-wider">Tilapia Depot</h2>
            <p class="text-xs mt-1">Kisumu-Busia Highway, Kisumu</p>
            <p class="text-xs">Tel: +254 712 345 678</p>
            <div class="border-b border-dashed border-gray-400 my-2"></div>
        </div>

        <!-- Meta Information -->
        <div class="space-y-1 text-xs mb-4">
            <div class="flex justify-between">
                <span>Receipt:</span>
                <span class="font-semibold"><?php echo $sale['sale_ref']; ?></span>
            </div>
            <div class="flex justify-between">
                <span>Date:</span>
                <span><?php echo date('d-M-Y H:i', strtotime($sale['created_at'])); ?></span>
            </div>
            <div class="flex justify-between">
                <span>Cashier:</span>
                <span class="truncate max-w-[150px]"><?php echo htmlspecialchars($sale['cashier_name']); ?></span>
            </div>
            <div class="flex justify-between">
                <span>Type:</span>
                <span class="uppercase"><?php echo htmlspecialchars($sale['customer_type']); ?></span>
            </div>
            <?php if (!empty($sale['customer_name'])): ?>
                <div class="flex justify-between">
                    <span>Customer:</span>
                    <span class="font-semibold truncate max-w-[150px]"><?php echo htmlspecialchars($sale['customer_name']); ?></span>
                </div>
            <?php endif; ?>
            <div class="border-b border-dashed border-gray-400 my-2"></div>
        </div>

        <!-- Cart Items Table -->
        <table class="w-full text-xs mb-4">
            <thead>
                <tr class="font-bold border-b border-dashed border-gray-400">
                    <th class="text-left pb-1">Item Description</th>
                    <th class="text-right pb-1">Qty</th>
                    <th class="text-right pb-1">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="align-top">
                        <td class="py-1">
                            <span class="block font-semibold"><?php echo htmlspecialchars($item['fish_name']); ?></span>
                            <span class="block text-[11px] text-gray-500 font-sans">
                                size: <?php echo htmlspecialchars($item['size']); ?> (<?php echo ucfirst($item['type']); ?>) @ Ksh <?php echo number_format($item['unit_price'], 2); ?>
                            </span>
                        </td>
                        <td class="text-right py-1"><?php echo $item['quantity']; ?></td>
                        <td class="text-right py-1 font-semibold">Ksh <?php echo number_format($item['line_total'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Summary Totals -->
        <div class="border-t border-dashed border-gray-400 pt-2 space-y-1 text-xs">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>Ksh <?php echo number_format($sale['subtotal'], 2); ?></span>
            </div>
            <?php if ($sale['discount'] > 0): ?>
                <div class="flex justify-between text-green-700">
                    <span>Discount:</span>
                    <span>-Ksh <?php echo number_format($sale['discount'], 2); ?></span>
                </div>
            <?php endif; ?>
            <div class="flex justify-between font-bold text-sm border-t border-dashed border-gray-400 pt-2">
                <span>TOTAL:</span>
                <span>Ksh <?php echo number_format($sale['total'], 2); ?></span>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="border-t border-dashed border-gray-400 mt-2 pt-2 space-y-1 text-xs">
            <div class="flex justify-between">
                <span>Payment Method:</span>
                <span class="font-bold uppercase"><?php echo htmlspecialchars($sale['payment_method']); ?></span>
            </div>
            <?php if ($sale['payment_method'] === 'cash'): ?>
                <div class="flex justify-between">
                    <span>Cash Tendered:</span>
                    <span>Ksh <?php echo number_format($sale['amount_tendered'], 2); ?></span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>Change Given:</span>
                    <span>Ksh <?php echo number_format($sale['change_given'], 2); ?></span>
                </div>
            <?php elseif ($sale['payment_method'] === 'mpesa'): ?>
                <div class="flex justify-between">
                    <span>M-Pesa Reference:</span>
                    <span class="font-bold"><?php echo htmlspecialchars($sale['mpesa_ref']); ?></span>
                </div>
            <?php elseif ($sale['payment_method'] === 'credit'): ?>
                <div class="flex justify-between font-bold text-red-600">
                    <span>Charged to Credit</span>
                    <span>A/C Ledger</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Notes -->
        <div class="border-t border-dashed border-gray-400 mt-4 pt-4 text-center text-[11px]">
            <p class="font-bold">Thank you for your business!</p>
            <p class="mt-1">Fish fried on order. Enjoy fresh Tilapia!</p>
            <p class="mt-2 text-[10px] text-gray-500 font-sans">System design: Antigravity AI</p>
        </div>
    </div>
    
</body>
</html>
