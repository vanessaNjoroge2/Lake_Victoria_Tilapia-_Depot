<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/ReportController.php';

$authController = new AuthController();
// Enforce admin/staff permission for reporting
$authController->requireRole(['admin', 'staff']);

$reportController = new ReportController();

// Determine date range
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Preset helpers
$rangeType = $_GET['range_type'] ?? '7days';
if (isset($_GET['preset'])) {
    $preset = $_GET['preset'];
    switch ($preset) {
        case 'today':
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
            $rangeType = 'today';
            break;
        case 'yesterday':
            $startDate = date('Y-m-d', strtotime('yesterday'));
            $endDate = date('Y-m-d', strtotime('yesterday'));
            $rangeType = 'yesterday';
            break;
        case 'thismonth':
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-d');
            $rangeType = 'thismonth';
            break;
        case '7days':
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
            $rangeType = '7days';
            break;
        case '30days':
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d');
            $rangeType = '30days';
            break;
    }
}

// Fetch report metrics
$kpis = $reportController->getDashboardKPIs($startDate, $endDate);
$salesBySize = $reportController->getSalesBySizeAndType($startDate, $endDate);
$cashiers = $reportController->getCashierPerformance($startDate, $endDate);

// Structure data for Chart.js
$chartLabels = [];
$chartRawData = [];
$chartFriedData = [];

// Prepare mapping
$sizeLabels = ['Size 1', 'Size 2', 'Size 3', 'Size 4', 'Size 5'];
foreach ($sizeLabels as $sz) {
    $chartLabels[] = $sz;
    $rawVal = 0;
    $friedVal = 0;
    foreach ($salesBySize as $item) {
        if ($item['size'] === $sz) {
            if ($item['type'] === 'raw') {
                $rawVal = intval($item['total_qty']);
            } else {
                $friedVal = intval($item['total_qty']);
            }
        }
    }
    $chartRawData[] = $rawVal;
    $chartFriedData[] = $friedVal;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Reports - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar Layout -->
    <?php include '../includes/staff_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Dashboard Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">POS Operations Reports</h1>
                    <p class="text-gray-600">Financial audits, frying margins, wastage analytics, and cashier ledger stats</p>
                </div>
                <div class="text-sm text-gray-500 bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2 font-medium">
                    <i class="fas fa-calendar-check text-blue-600"></i>
                    <span>Running Audit: <strong><?php echo date('d M Y', strtotime($startDate)); ?></strong> to <strong><?php echo date('d M Y', strtotime($endDate)); ?></strong></span>
                </div>
            </div>

            <!-- Date Filtering Panel -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <!-- Preset Options -->
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <a href="?preset=today&range_type=today" 
                           class="px-4 py-2.5 rounded-xl border transition <?php echo $rangeType === 'today' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200'; ?>">
                            <i class="fas fa-clock mr-1"></i> Today
                        </a>
                        <a href="?preset=yesterday&range_type=yesterday" 
                           class="px-4 py-2.5 rounded-xl border transition <?php echo $rangeType === 'yesterday' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200'; ?>">
                            <i class="fas fa-history mr-1"></i> Yesterday
                        </a>
                        <a href="?preset=7days&range_type=7days" 
                           class="px-4 py-2.5 rounded-xl border transition <?php echo $rangeType === '7days' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200'; ?>">
                            <i class="fas fa-calendar-week mr-1"></i> Last 7 Days
                        </a>
                        <a href="?preset=30days&range_type=30days" 
                           class="px-4 py-2.5 rounded-xl border transition <?php echo $rangeType === '30days' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200'; ?>">
                            <i class="fas fa-calendar-alt mr-1"></i> Last 30 Days
                        </a>
                        <a href="?preset=thismonth&range_type=thismonth" 
                           class="px-4 py-2.5 rounded-xl border transition <?php echo $rangeType === 'thismonth' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200'; ?>">
                            <i class="fas fa-calendar-day mr-1"></i> This Month
                        </a>
                    </div>
                    
                    <!-- Custom Range Form -->
                    <form method="GET" action="" class="flex items-center gap-3 text-xs font-bold w-full lg:w-auto">
                        <input type="hidden" name="range_type" value="custom">
                        <div class="flex items-center gap-2">
                            <label class="text-slate-500 uppercase tracking-wider">From</label>
                            <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" 
                                   class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-slate-500 uppercase tracking-wider">To</label>
                            <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" 
                                   class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                        </div>
                        <button type="submit" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition shadow-sm">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </form>
                </div>
            </div>

            <!-- KPI Metric Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 text-xs">
                <!-- Total Gross Revenue -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-slate-400 font-bold uppercase tracking-wider mb-1">Gross Revenue</span>
                            <span class="text-2xl font-bold text-slate-800">Ksh <?php echo number_format($kpis['financials']['revenue'] ?? 0, 2); ?></span>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                            <i class="fas fa-hand-holding-dollar text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between font-semibold">
                        <span class="text-slate-500">Completed Orders:</span>
                        <span class="text-slate-800"><?php echo $kpis['sales']['total_orders'] ?? 0; ?></span>
                    </div>
                </div>

                <!-- Cash vs Mpesa Breakdown -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-slate-400 font-bold uppercase tracking-wider mb-1">Payment Splits</span>
                            <span class="text-xs font-bold text-slate-700 flex flex-col gap-0.5">
                                <span class="text-green-600"><i class="fas fa-money-bill mr-1"></i> Cash: Ksh <?php echo number_format($kpis['sales']['sales_cash'] ?? 0, 2); ?></span>
                                <span class="text-cyan-600"><i class="fas fa-mobile-screen mr-1.5"></i> M-Pesa: Ksh <?php echo number_format($kpis['sales']['sales_mpesa'] ?? 0, 2); ?></span>
                            </span>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                            <i class="fas fa-cash-register text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between font-semibold">
                        <span class="text-slate-500">Credit Invoiced:</span>
                        <span class="text-blue-600">Ksh <?php echo number_format($kpis['sales']['sales_credit'] ?? 0, 2); ?></span>
                    </div>
                </div>

                <!-- Frying Conversion Overhead -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-slate-400 font-bold uppercase tracking-wider mb-1">Frying Overheads</span>
                            <span class="text-2xl font-bold text-slate-800">Ksh <?php echo number_format($kpis['financials']['frying_expenses'] ?? 0, 2); ?></span>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-xl text-amber-600">
                            <i class="fas fa-fire-burner text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between font-semibold">
                        <span class="text-slate-500">Batches completed:</span>
                        <span class="text-amber-700"><?php echo $kpis['frying']['total_frying_batches'] ?? 0; ?> (<?php echo $kpis['frying']['total_fried_pieces'] ?? 0; ?> pcs)</span>
                    </div>
                </div>

                <!-- Wastage Total Loss -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-slate-400 font-bold uppercase tracking-wider mb-1">Wastage Loss</span>
                            <span class="text-2xl font-bold text-red-600">Ksh <?php echo number_format($kpis['financials']['wastage_loss'] ?? 0, 2); ?></span>
                        </div>
                        <div class="p-3 bg-red-50 rounded-xl text-red-600">
                            <i class="fas fa-trash-alt text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between font-semibold">
                        <span class="text-slate-500">Spoiled Inventory:</span>
                        <span class="text-red-700"><?php echo $kpis['wastage']['total_wastage_quantity'] ?? 0; ?> pieces</span>
                    </div>
                </div>
            </div>

            <!-- Financial Margin Block -->
            <div class="bg-gradient-to-r from-blue-900 to-indigo-950 text-white rounded-2xl shadow-sm border border-slate-800 p-8 mb-8">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-8">
                    <div>
                        <span class="bg-blue-800/80 text-blue-200 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider mb-2 inline-block"><i class="fas fa-money-check-dollar mr-1"></i> Net Operating Margin</span>
                        <h2 class="text-3xl font-bold mb-2">Estimated Net Profit Margin: <span class="text-green-400"><?php echo number_format($kpis['financials']['gross_margin_percent'] ?? 0, 2); ?>%</span></h2>
                        <p class="text-xs text-blue-200/80 leading-relaxed max-w-xl">Calculated from total sales revenue minus acquisitions COGS (Ksh <?php echo number_format($kpis['financials']['cogs'] ?? 0, 2); ?>), frying operations (Ksh <?php echo number_format($kpis['financials']['frying_expenses'] ?? 0, 2); ?>) and wastage write-offs (Ksh <?php echo number_format($kpis['financials']['wastage_loss'] ?? 0, 2); ?>).</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/10 text-right w-full lg:w-auto">
                        <span class="block text-[10px] text-blue-200 font-bold uppercase mb-1">Operating Profit</span>
                        <span class="text-3xl font-bold text-green-400 block mb-1">Ksh <?php echo number_format($kpis['financials']['gross_profit'] ?? 0, 2); ?></span>
                        <span class="text-xs text-blue-200 block">After inventory & expense offsets</span>
                    </div>
                </div>
            </div>

            <!-- Chart & Size Analysis Segment -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8 text-xs">
                <!-- Size Chart Widget -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
                    <h3 class="text-slate-800 font-bold text-sm mb-4 uppercase tracking-wider"><i class="fas fa-chart-bar text-blue-600 mr-1.5"></i> Sales volume by Size & Classification</h3>
                    <div class="relative h-72">
                        <canvas id="sizeChart"></canvas>
                    </div>
                </div>

                <!-- Size Analysis Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
                    <h3 class="text-slate-800 font-bold text-sm mb-4 uppercase tracking-wider"><i class="fas fa-table-list text-purple-600 mr-1.5"></i> Detailed Size Metrics</h3>
                    <div class="flex-1 overflow-y-auto">
                        <?php if (empty($salesBySize)): ?>
                            <div class="h-full flex flex-col items-center justify-center text-slate-400">
                                <i class="fas fa-chart-line text-3xl mb-2"></i>
                                <span class="font-bold">No sales in date range</span>
                            </div>
                        <?php else: ?>
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[10px] text-slate-400 uppercase font-bold">
                                        <th class="pb-2">Size / State</th>
                                        <th class="pb-2 text-right">Sold Qty</th>
                                        <th class="pb-2 text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 font-semibold">
                                    <?php foreach ($salesBySize as $item): ?>
                                        <tr>
                                            <td class="py-2.5 flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo $item['type'] === 'raw' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600'; ?>">
                                                    <?php echo strtoupper($item['type']); ?>
                                                </span>
                                                <span class="text-slate-700"><?php echo htmlspecialchars($item['size']); ?></span>
                                            </td>
                                            <td class="py-2.5 text-right text-slate-800"><?php echo $item['total_qty']; ?></td>
                                            <td class="py-2.5 text-right text-slate-900">Ksh <?php echo number_format($item['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cashier Ledger & Stats -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 text-xs">
                <h3 class="text-slate-800 font-bold text-sm mb-4 uppercase tracking-wider"><i class="fas fa-users-viewfinder text-blue-600 mr-1.5"></i> Cashier Drawer Audits & Void Activities</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 uppercase font-bold">
                                <th class="pb-3">Cashier / Staff Member</th>
                                <th class="pb-3 text-center">Completed Sales</th>
                                <th class="pb-3 text-right">Total Revenue</th>
                                <th class="pb-3 text-center">Voids Logged</th>
                                <th class="pb-3 text-right">Voided Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 font-semibold text-slate-700">
                            <?php if (empty($cashiers)): ?>
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-400">No staff checkouts recorded in this period.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cashiers as $c): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3">
                                            <div class="flex flex-col">
                                                <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($c['cashier_name']); ?></span>
                                                <span class="text-[10px] text-slate-400">@<?php echo htmlspecialchars($c['cashier_username']); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center text-slate-800"><?php echo $c['completed_sales_count']; ?></td>
                                        <td class="py-3 text-right text-emerald-600 font-bold">Ksh <?php echo number_format($c['total_revenue'], 2); ?></td>
                                        <td class="py-3 text-center text-red-600"><?php echo $c['voided_sales_count']; ?></td>
                                        <td class="py-3 text-right text-slate-500">Ksh <?php echo number_format($c['voided_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Chart Configuration Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('sizeChart').getContext('2d');
            
            const rawData = <?php echo json_encode($chartRawData); ?>;
            const friedData = <?php echo json_encode($chartFriedData); ?>;
            const labels = <?php echo json_encode($chartLabels); ?>;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Raw Fish Class',
                            data: rawData,
                            backgroundColor: '#2563eb', // blue-600
                            borderRadius: 8,
                            borderSkipped: false
                        },
                        {
                            label: 'Fried Fish Class',
                            data: friedData,
                            backgroundColor: '#d97706', // amber-600
                            borderRadius: 8,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 20,
                                font: {
                                    family: 'Poppins',
                                    weight: 'bold',
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
