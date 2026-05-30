<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../includes/csrf.php';

$authController = new AuthController();
$authController->requireRole(['admin', 'staff']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Cashier - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Custom scrollbar for premium feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex overflow-hidden">

    <!-- Sidebar Layout -->
    <?php include '../includes/staff_sidebar.php'; ?>

    <!-- Main Workspace Container -->
    <div class="flex-1 ml-64 flex flex-col h-screen overflow-hidden">
        
        <!-- Top Cashier Header -->
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between flex-shrink-0 z-30">
            <div class="flex items-center space-x-4">
                <h1 class="text-xl font-bold text-slate-800 flex items-center">
                    <i class="fas fa-cash-register text-blue-600 mr-3"></i>
                    POS Cashier Station
                </h1>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                    Terminal Active
                </span>
            </div>
            
            <div class="flex items-center space-x-6">
                <!-- Live Clock -->
                <div class="text-sm font-medium text-slate-500 hidden md:block" id="live-clock">
                    <i class="far fa-clock mr-2"></i>Loading time...
                </div>
                
                <!-- Active Cashier Profile -->
                <div class="flex items-center space-x-3 border-l border-slate-200 pl-6">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 2)); ?>
                    </div>
                    <div class="text-left">
                        <p class="text-xs text-slate-400 font-semibold leading-tight">CASHIER</p>
                        <p class="text-sm font-bold text-slate-700 leading-tight"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                    </div>
                </div>
            </div>
        </header>

        <!-- POS Interactive Body -->
        <div class="flex-1 flex overflow-hidden bg-slate-100">
            
            <!-- LEFT PANEL: Product Selector & Category Navigator -->
            <div class="w-7/12 flex flex-col h-full border-r border-slate-200 flex-shrink-0">
                <!-- Filters & Search Bar -->
                <div class="p-4 bg-white border-b border-slate-200 space-y-3 flex-shrink-0">
                    <div class="flex gap-3">
                        <!-- Product Search -->
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400"></i>
                            <input type="text" id="product-search" placeholder="Search by fish size, name or description..."
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition duration-150">
                        </div>
                        
                        <!-- Categorized Tabs (Raw / Fried) -->
                        <div class="bg-slate-100 p-1 rounded-xl flex gap-1">
                            <button onclick="filterType('all')" id="btn-type-all" class="px-4 py-2 text-xs font-semibold rounded-lg bg-white text-slate-700 shadow-sm transition duration-150">
                                All
                            </button>
                            <button onclick="filterType('raw')" id="btn-type-raw" class="px-4 py-2 text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-800 transition duration-150">
                                Raw
                            </button>
                            <button onclick="filterType('fried')" id="btn-type-fried" class="px-4 py-2 text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-800 transition duration-150">
                                Fried
                            </button>
                        </div>
                    </div>

                    <!-- Size Category Badges -->
                    <div class="flex gap-2 overflow-x-auto pb-1" id="size-filters">
                        <!-- Filled dynamically -->
                    </div>
                </div>

                <!-- Product Grid (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="product-grid">
                        <!-- Dynamic Product Cards -->
                        <div class="col-span-full py-12 flex flex-col items-center justify-center text-slate-400">
                            <i class="fas fa-circle-notch fa-spin text-3xl text-blue-500 mb-3"></i>
                            <p>Syncing products inventory...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: Checkout Cart Panel -->
            <div class="w-5/12 flex flex-col h-full bg-white">
                
                <!-- Customer Section -->
                <div class="p-4 border-b border-slate-200 space-y-3 flex-shrink-0 bg-slate-50">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Customer Profile</label>
                        <!-- Retail / Wholesale Switcher -->
                        <div class="flex bg-slate-200 p-0.5 rounded-lg text-xs font-semibold">
                            <button onclick="switchCustomerType('retail')" id="btn-cust-retail" class="px-3 py-1.5 rounded-md bg-white text-slate-800 shadow-sm transition duration-150">
                                Retail
                            </button>
                            <button onclick="switchCustomerType('wholesale')" id="btn-cust-wholesale" class="px-3 py-1.5 rounded-md text-slate-600 hover:text-slate-800 transition duration-150">
                                Wholesale
                            </button>
                        </div>
                    </div>

                    <!-- Wholesale Autocomplete Search -->
                    <div id="customer-search-container" class="relative hidden">
                        <i class="fas fa-user-tag absolute left-3 top-3 text-slate-400"></i>
                        <input type="text" id="customer-query" placeholder="Search wholesale customer (name/phone/email)..."
                               class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button onclick="clearSelectedCustomer()" id="btn-clear-cust" class="absolute right-3 top-3 text-slate-400 hover:text-red-500 hidden">
                            <i class="fas fa-times-circle"></i>
                        </button>
                        <!-- Autocomplete dropdown -->
                        <div id="customer-results" class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 shadow-xl rounded-xl max-h-48 overflow-y-auto hidden z-50"></div>
                    </div>

                    <!-- Walk-in fields for Retail -->
                    <div id="retail-walkin-container" class="grid grid-cols-2 gap-2">
                        <input type="text" id="walkin-name" placeholder="Walk-in Name (Optional)"
                               class="bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="text" id="walkin-phone" placeholder="Walk-in Phone (Optional)"
                               class="bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Selected Customer Alert Card -->
                    <div id="selected-customer-card" class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-900 hidden">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-sm" id="card-cust-name"></p>
                                <p id="card-cust-phone" class="text-blue-700 font-medium"></p>
                            </div>
                            <button onclick="clearSelectedCustomer()" class="text-blue-500 hover:text-red-600">
                                <i class="fas fa-times-circle text-base"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-blue-200/50">
                            <div>
                                <span class="text-blue-600 block">Credit Limit:</span>
                                <span class="font-semibold block" id="card-cust-limit">Ksh 0.00</span>
                            </div>
                            <div>
                                <span class="text-blue-600 block">Outstanding:</span>
                                <span class="font-semibold text-red-600 block" id="card-cust-balance">Ksh 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3" id="cart-container">
                    <!-- Dynamic Cart Rows -->
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 py-16">
                        <i class="fas fa-shopping-basket text-4xl mb-3 text-slate-300"></i>
                        <p class="text-sm font-medium">Checkout Basket is Empty</p>
                        <p class="text-xs text-slate-400 mt-1">Select products from the left to start sale.</p>
                    </div>
                </div>

                <!-- Checkout Summary & Tabs -->
                <div class="border-t border-slate-200 p-4 bg-slate-50 flex-shrink-0 space-y-4">
                    
                    <!-- Subtotal, Discount & Total -->
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span id="cart-subtotal" class="font-semibold">Ksh 0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Discount (Ksh)</span>
                            <input type="number" id="cart-discount" value="0" min="0" oninput="calculateCartTotals()"
                                   class="w-20 text-right bg-white border border-slate-200 rounded px-2 py-0.5 font-semibold text-sm">
                        </div>
                        <div class="flex justify-between text-lg font-bold text-slate-800 pt-2 border-t border-slate-200/60">
                            <span>Total Payable</span>
                            <span id="cart-total" class="text-blue-600">Ksh 0.00</span>
                        </div>
                    </div>

                    <!-- Payment Method Tabs -->
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Payment Option</label>
                        <div class="grid grid-cols-3 gap-2 bg-slate-200 p-1 rounded-xl text-xs font-semibold text-center">
                            <button onclick="setPaymentMethod('cash')" id="btn-pay-cash" class="py-2.5 rounded-lg bg-white text-slate-800 shadow-sm flex flex-col items-center gap-1 transition duration-150">
                                <i class="fas fa-money-bill-wave text-green-500"></i>
                                <span>Cash</span>
                            </button>
                            <button onclick="setPaymentMethod('mpesa')" id="btn-pay-mpesa" class="py-2.5 rounded-lg text-slate-600 hover:text-slate-800 flex flex-col items-center gap-1 transition duration-150">
                                <i class="fas fa-mobile-screen-button text-purple-600"></i>
                                <span>M-Pesa</span>
                            </button>
                            <button onclick="setPaymentMethod('credit')" id="btn-pay-credit" class="py-2.5 rounded-lg text-slate-600 hover:text-slate-800 flex flex-col items-center gap-1 transition duration-150">
                                <i class="fas fa-hand-holding-dollar text-red-500"></i>
                                <span>On Credit</span>
                            </button>
                        </div>
                    </div>

                    <!-- Interactive Method Forms -->
                    <div>
                        <!-- CASH PAYMENT: Cash Tendered & Change -->
                        <div id="form-pay-cash" class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 block mb-1">Cash Tendered</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-slate-400 text-xs font-bold">Ksh</span>
                                        <input type="number" id="pay-cash-tendered" placeholder="0.00" oninput="calculateCashChange()"
                                               class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 block mb-1">Change Due</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-slate-400 text-xs font-bold">Ksh</span>
                                        <input type="text" id="pay-cash-change" value="0.00" readonly
                                               class="w-full bg-slate-100 border border-slate-200 rounded-xl pl-10 pr-3 py-2 text-sm font-bold text-slate-700">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- M-PESA PAYMENT: STK Push -->
                        <div id="form-pay-mpesa" class="space-y-3 hidden">
                            <div class="grid grid-cols-1 gap-2">
                                <label class="text-xs font-semibold text-slate-500 block">Safaricom Customer Phone</label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <i class="fas fa-phone absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                        <input type="text" id="pay-mpesa-phone" placeholder="e.g. 0712345678"
                                               class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <button onclick="triggerMpesaSTK()" id="btn-stk-push" class="bg-purple-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-purple-700 flex items-center gap-1 transition duration-150">
                                        <i class="fas fa-paper-plane"></i>
                                        <span>STK Push</span>
                                    </button>
                                </div>
                                <div id="mpesa-status-box" class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-xs text-purple-900 hidden flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-spinner fa-spin text-purple-600 text-base" id="mpesa-spinner"></i>
                                        <span id="mpesa-status-msg">Initiating STK Push...</span>
                                    </div>
                                    <button onclick="cancelMpesaPoll()" class="text-purple-500 hover:text-red-600 font-bold">Cancel</button>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 block mb-1">Manual M-Pesa Receipt Code (Fallback)</label>
                                    <input type="text" id="pay-mpesa-ref" placeholder="e.g. QWE123RTY4"
                                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold uppercase placeholder-normal placeholder-slate-300">
                                </div>
                            </div>
                        </div>

                        <!-- ON CREDIT PAYMENT -->
                        <div id="form-pay-credit" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-900 hidden">
                            <i class="fas fa-circle-exclamation mr-1"></i>
                            <span>This order will be charged directly to the outstanding credit balance of the selected wholesale customer account. Payment is expected within agreed credit terms.</span>
                        </div>
                    </div>

                    <!-- Notes Input -->
                    <div>
                        <input type="text" id="cart-notes" placeholder="Add transaction notes / special requests..."
                               class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Primary Action Buttons -->
                    <div class="grid grid-cols-4 gap-3 pt-2">
                        <button onclick="clearCart()" class="col-span-1 bg-slate-200 text-slate-700 py-3 rounded-xl text-xs font-bold hover:bg-red-100 hover:text-red-700 transition duration-150 flex items-center justify-center gap-1">
                            <i class="fas fa-trash-alt"></i>
                            <span class="hidden sm:inline">Clear</span>
                        </button>
                        <button onclick="checkoutSale()" id="btn-checkout" class="col-span-3 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-md hover:shadow-lg transition duration-150 flex items-center justify-center gap-2">
                            <i class="fas fa-check-double"></i>
                            <span>COMPLETE CHECKOUT</span>
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Inject CSRF token to window for AJAX requests -->
    <script>
        window.csrfToken = "<?php echo generate_csrf_token(); ?>";
        window.baseUrl = "<?php echo BASE_URL; ?>";
    </script>

    <!-- POS Vanilla ES6 Logic -->
    <script>
        // State variables
        let products = [];
        let sizes = [];
        let activeType = 'all';
        let activeSize = 'all';
        let customerType = 'retail'; // 'retail' or 'wholesale'
        let selectedCustomer = null;
        let paymentMethod = 'cash'; // 'cash', 'mpesa', 'credit'
        let cart = [];
        let mpesaPollInterval = null;
        let activeCheckoutRequestId = null;

        // Fetch products inventory from PosController
        async function fetchProducts() {
            try {
                const response = await fetch(`${window.baseUrl}/controllers/PosController.php?action=get_products`);
                const data = await response.json();
                if (data.success) {
                    products = data.products;
                    // Extract unique sizes
                    const allSizes = products.map(p => p.size).filter(Boolean);
                    sizes = ['all', ...new Set(allSizes)];
                    renderSizeFilters();
                    renderProducts();
                } else {
                    alert('Error loading products: ' + data.message);
                }
            } catch (err) {
                console.error('Failed to sync products', err);
            }
        }

        // Render category filters
        function renderSizeFilters() {
            const container = document.getElementById('size-filters');
            container.innerHTML = sizes.map(size => {
                const isActive = activeSize === size;
                const label = size === 'all' ? 'All Sizes' : size;
                return `
                    <button onclick="filterSize('${size}')" 
                            class="px-3.5 py-1.5 text-xs font-medium rounded-full whitespace-nowrap transition duration-150 flex-shrink-0 
                                   ${isActive ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'}">
                        ${label}
                    </button>
                `;
            }).join('');
        }

        // Render product cards
        function renderProducts() {
            const container = document.getElementById('product-grid');
            const searchVal = document.getElementById('product-search').value.toLowerCase();

            // Filter logic
            const filtered = products.filter(p => {
                const matchType = activeType === 'all' || p.type === activeType;
                const matchSize = activeSize === 'all' || p.size === activeSize;
                const matchSearch = p.name.toLowerCase().includes(searchVal) || 
                                    p.size.toLowerCase().includes(searchVal) || 
                                    p.type.toLowerCase().includes(searchVal);
                return matchType && matchSize && matchSearch;
            });

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full py-12 text-center text-slate-400">
                        <i class="fas fa-search text-3xl mb-2"></i>
                        <p class="text-sm">No products found matching filters.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filtered.map(p => {
                const price = customerType === 'wholesale' ? parseFloat(p.wholesale_price) : parseFloat(p.retail_price);
                const stock = parseInt(p.stock_qty);
                const isLow = stock <= parseInt(p.low_stock_threshold);
                const isOutOfStock = stock <= 0;
                
                // Colors and badges
                let cardClass = "bg-white border border-slate-200 hover:border-blue-400 hover:shadow-lg transition duration-200 rounded-2xl p-4 cursor-pointer relative flex flex-col justify-between";
                let stockBadgeClass = isLow ? "bg-red-100 text-red-800" : "bg-green-100 text-green-800";
                
                if (isOutOfStock) {
                    cardClass = "bg-slate-50 border border-slate-200 opacity-60 rounded-2xl p-4 relative flex flex-col justify-between cursor-not-allowed";
                }

                const action = isOutOfStock ? "" : `onclick="addToCart(${p.id})"`;

                return `
                    <div ${action} class="${cardClass}">
                        <div>
                            <!-- Header Info -->
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full ${p.type === 'raw' ? 'bg-cyan-100 text-cyan-800' : 'bg-amber-100 text-amber-800'}">
                                    ${p.type}
                                </span>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full ${stockBadgeClass}">
                                    ${isOutOfStock ? 'OUT OF STOCK' : `Stock: ${stock} ${p.unit}s`}
                                </span>
                            </div>
                            
                            <!-- Name & Size -->
                            <h3 class="font-bold text-slate-800 text-sm truncate">${p.name}</h3>
                            <p class="text-slate-400 text-xs mt-0.5 font-medium">Size: ${p.size}</p>
                        </div>

                        <!-- Footer Price -->
                        <div class="flex justify-between items-center mt-4 pt-2 border-t border-slate-100">
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase">${customerType === 'wholesale' ? 'Wholesale' : 'Retail'}</p>
                                <p class="font-bold text-blue-600 text-base">Ksh ${price.toFixed(2)}</p>
                            </div>
                            <div class="bg-blue-50 hover:bg-blue-600 hover:text-white p-2 rounded-xl text-blue-600 transition duration-150">
                                <i class="fas fa-plus text-xs"></i>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Set category filter state
        function filterType(type) {
            activeType = type;
            ['all', 'raw', 'fried'].forEach(t => {
                const btn = document.getElementById(`btn-type-${t}`);
                if (t === type) {
                    btn.classList.add('bg-white', 'text-slate-700', 'shadow-sm');
                    btn.classList.remove('text-slate-600');
                } else {
                    btn.classList.remove('bg-white', 'text-slate-700', 'shadow-sm');
                    btn.classList.add('text-slate-600');
                }
            });
            renderProducts();
        }

        function filterSize(size) {
            activeSize = size;
            renderSizeFilters();
            renderProducts();
        }

        // Search trigger
        document.getElementById('product-search').addEventListener('input', renderProducts);

        // Switch Customer Type
        function switchCustomerType(type) {
            customerType = type;
            const btnRetail = document.getElementById('btn-cust-retail');
            const btnWholesale = document.getElementById('btn-cust-wholesale');
            const searchContainer = document.getElementById('customer-search-container');
            const walkinContainer = document.getElementById('retail-walkin-container');
            const payCreditTab = document.getElementById('btn-pay-credit');

            if (type === 'retail') {
                btnRetail.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                btnRetail.classList.remove('text-slate-600');
                btnWholesale.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                btnWholesale.classList.add('text-slate-600');

                searchContainer.classList.add('hidden');
                walkinContainer.classList.remove('hidden');
                
                // Hide and redirect credit pay method since it's restricted
                payCreditTab.classList.add('opacity-40', 'cursor-not-allowed');
                if (paymentMethod === 'credit') {
                    setPaymentMethod('cash');
                }
                clearSelectedCustomer();
            } else {
                btnWholesale.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                btnWholesale.classList.remove('text-slate-600');
                btnRetail.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                btnRetail.classList.add('text-slate-600');

                searchContainer.classList.remove('hidden');
                walkinContainer.classList.add('hidden');
                
                payCreditTab.classList.remove('opacity-40', 'cursor-not-allowed');
            }
            renderProducts();
            renderCart();
        }

        // Customer Autocomplete Search
        const customerInput = document.getElementById('customer-query');
        const customerResults = document.getElementById('customer-results');

        customerInput.addEventListener('input', async () => {
            const query = customerInput.value.trim();
            if (query.length < 2) {
                customerResults.innerHTML = '';
                customerResults.classList.add('hidden');
                return;
            }

            try {
                const response = await fetch(`${window.baseUrl}/controllers/PosController.php?action=search_customers&query=${encodeURIComponent(query)}`);
                const data = await response.json();
                if (data.success && data.customers.length > 0) {
                    customerResults.innerHTML = data.customers.map(c => `
                        <div onclick="selectCustomer(${JSON.stringify(c).replace(/"/g, '&quot;')})" 
                             class="p-2.5 border-b border-slate-100 hover:bg-slate-50 cursor-pointer text-xs">
                            <p class="font-bold text-slate-700">${c.full_name}</p>
                            <p class="text-slate-500">${c.phone} | outstanding: Ksh ${parseFloat(c.outstanding_balance).toFixed(2)}</p>
                        </div>
                    `).join('');
                    customerResults.classList.remove('hidden');
                } else {
                    customerResults.innerHTML = '<div class="p-3 text-xs text-slate-400 text-center">No customer accounts found</div>';
                    customerResults.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
            }
        });

        function selectCustomer(cust) {
            selectedCustomer = cust;
            customerType = cust.customer_type || 'wholesale';
            
            // Hide input container, show card details
            document.getElementById('customer-search-container').classList.add('hidden');
            document.getElementById('selected-customer-card').classList.remove('hidden');
            
            document.getElementById('card-cust-name').innerText = cust.full_name;
            document.getElementById('card-cust-phone').innerText = cust.phone || 'No phone';
            document.getElementById('card-cust-limit').innerText = `Ksh ${parseFloat(cust.credit_limit).toFixed(2)}`;
            document.getElementById('card-cust-balance').innerText = `Ksh ${parseFloat(cust.outstanding_balance).toFixed(2)}`;
            
            // Fill phone field for STK push
            if (cust.phone) {
                document.getElementById('pay-mpesa-phone').value = cust.phone;
            }

            customerResults.innerHTML = '';
            customerResults.classList.add('hidden');
            customerInput.value = '';

            renderProducts();
            renderCart();
        }

        function clearSelectedCustomer() {
            selectedCustomer = null;
            customerType = 'retail';
            document.getElementById('customer-search-container').classList.remove('hidden');
            document.getElementById('selected-customer-card').classList.add('hidden');
            document.getElementById('pay-mpesa-phone').value = '';
            renderProducts();
            renderCart();
        }

        // Cart Actions
        function addToCart(productId) {
            const product = products.find(p => p.id === productId);
            if (!product) return;

            const existing = cart.find(item => item.fish_id === productId);
            if (existing) {
                if (existing.quantity >= parseInt(product.stock_qty)) {
                    alert(`Insufficient stock! Cannot add more of '${product.name}'.`);
                    return;
                }
                existing.quantity++;
            } else {
                if (parseInt(product.stock_qty) <= 0) {
                    alert('Product is out of stock.');
                    return;
                }
                cart.push({
                    fish_id: product.id,
                    name: product.name,
                    size: product.size,
                    type: product.type,
                    unit_price: customerType === 'wholesale' ? parseFloat(product.wholesale_price) : parseFloat(product.retail_price),
                    quantity: 1,
                    stock_qty: parseInt(product.stock_qty)
                });
            }
            renderCart();
        }

        function updateCartQty(productId, qty) {
            const item = cart.find(i => i.fish_id === productId);
            if (!item) return;

            qty = parseInt(qty);
            if (isNaN(qty) || qty <= 0) {
                qty = 1;
            }

            if (qty > item.stock_qty) {
                alert(`Insufficient stock! Maximum available is ${item.stock_qty}.`);
                qty = item.stock_qty;
            }

            item.quantity = qty;
            renderCart();
        }

        function removeFromCart(productId) {
            cart = cart.filter(i => i.fish_id !== productId);
            renderCart();
        }

        function clearCart() {
            if (cart.length === 0) return;
            if (confirm('Are you sure you want to discard the active basket?')) {
                cart = [];
                document.getElementById('cart-discount').value = 0;
                document.getElementById('cart-notes').value = '';
                renderCart();
            }
        }

        // Render Cart Items
        function renderCart() {
            const container = document.getElementById('cart-container');
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 py-16">
                        <i class="fas fa-shopping-basket text-4xl mb-3 text-slate-300"></i>
                        <p class="text-sm font-medium">Checkout Basket is Empty</p>
                        <p class="text-xs text-slate-400 mt-1">Select products from the left to start sale.</p>
                    </div>
                `;
                calculateCartTotals();
                return;
            }

            // Sync unit prices if customer type has changed
            cart.forEach(item => {
                const prod = products.find(p => p.id === item.fish_id);
                if (prod) {
                    item.unit_price = customerType === 'wholesale' ? parseFloat(prod.wholesale_price) : parseFloat(prod.retail_price);
                }
            });

            container.innerHTML = cart.map(item => {
                const lineTotal = item.unit_price * item.quantity;
                return `
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200/80 rounded-xl p-3">
                        <div class="flex-1 min-w-0 pr-3">
                            <p class="font-bold text-slate-800 text-xs truncate">${item.name}</p>
                            <p class="text-[10px] text-slate-400 font-medium mt-0.5">Size: ${item.size} (${item.type})</p>
                            <p class="text-xs font-bold text-blue-600 mt-1">Ksh ${item.unit_price.toFixed(2)}</p>
                        </div>
                        
                        <!-- Qty Adjuster -->
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button onclick="adjustCartItemQty(${item.fish_id}, -1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-600">
                                <i class="fas fa-minus text-[10px]"></i>
                            </button>
                            <input type="number" value="${item.quantity}" min="1" max="${item.stock_qty}"
                                   onchange="updateCartQty(${item.fish_id}, this.value)"
                                   class="w-10 text-center bg-white border border-slate-200 rounded-lg py-1 font-bold text-xs">
                            <button onclick="adjustCartItemQty(${item.fish_id}, 1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-600">
                                <i class="fas fa-plus text-[10px]"></i>
                            </button>
                        </div>

                        <!-- Subtotal & Delete -->
                        <div class="text-right pl-3 min-w-[70px] flex flex-col items-end">
                            <span class="font-bold text-slate-800 text-xs">Ksh ${lineTotal.toFixed(2)}</span>
                            <button onclick="removeFromCart(${item.fish_id})" class="text-slate-400 hover:text-red-500 mt-1">
                                <i class="far fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            calculateCartTotals();
        }

        function adjustCartItemQty(productId, change) {
            const item = cart.find(i => i.fish_id === productId);
            if (item) {
                updateCartQty(productId, item.quantity + change);
            }
        }

        // Totals calculation
        let cartSubtotal = 0;
        let cartTotal = 0;

        function calculateCartTotals() {
            cartSubtotal = cart.reduce((acc, item) => acc + (item.unit_price * item.quantity), 0);
            
            const discountInput = document.getElementById('cart-discount');
            let discount = parseFloat(discountInput.value) || 0;
            if (discount < 0) {
                discount = 0;
                discountInput.value = 0;
            }
            if (discount > cartSubtotal) {
                discount = cartSubtotal;
                discountInput.value = cartSubtotal;
            }

            cartTotal = Math.max(0, cartSubtotal - discount);

            document.getElementById('cart-subtotal').innerText = `Ksh ${cartSubtotal.toFixed(2)}`;
            document.getElementById('cart-total').innerText = `Ksh ${cartTotal.toFixed(2)}`;

            calculateCashChange();
        }

        // Set active payment method
        function setPaymentMethod(method) {
            if (method === 'credit') {
                if (customerType !== 'wholesale' || !selectedCustomer) {
                    alert('Credit payment is restricted to active wholesale customer accounts.');
                    return;
                }
            }

            paymentMethod = method;

            ['cash', 'mpesa', 'credit'].forEach(m => {
                const btn = document.getElementById(`btn-pay-${m}`);
                const form = document.getElementById(`form-pay-${m}`);

                if (m === method) {
                    btn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                    btn.classList.remove('text-slate-600');
                    form.classList.remove('hidden');
                } else {
                    btn.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                    btn.classList.add('text-slate-600');
                    form.classList.add('hidden');
                }
            });
        }

        // Cash Change Due calculator
        function calculateCashChange() {
            const tendered = parseFloat(document.getElementById('pay-cash-tendered').value) || 0;
            const change = Math.max(0, tendered - cartTotal);
            document.getElementById('pay-cash-change').value = change.toFixed(2);
        }

        // M-PESA STK Push logic
        async function triggerMpesaSTK() {
            const phone = document.getElementById('pay-mpesa-phone').value.trim();
            if (!phone) {
                alert('Please enter a Safaricom phone number.');
                return;
            }

            if (cartTotal <= 0) {
                alert('Total payable must be greater than zero.');
                return;
            }

            const btn = document.getElementById('btn-stk-push');
            const statusBox = document.getElementById('mpesa-status-box');
            const statusMsg = document.getElementById('mpesa-status-msg');

            btn.disabled = true;
            statusBox.classList.remove('hidden');
            statusMsg.innerText = 'Sending STK Push trigger...';

            try {
                const response = await fetch(`${window.baseUrl}/controllers/PosController.php?action=trigger_mpesa`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: phone,
                        amount: cartTotal,
                        description: `Tilapia POS: ${selectedCustomer ? selectedCustomer.full_name : 'Retail'}`
                    })
                });

                const data = await response.json();
                if (data.success) {
                    activeCheckoutRequestId = data.checkout_request_id;
                    statusMsg.innerText = 'STK Push sent! Enter PIN on phone...';
                    
                    // Start polling
                    startMpesaPoll(activeCheckoutRequestId);
                } else {
                    alert('M-Pesa Trigger Failed: ' + data.message);
                    resetMpesaStatus();
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred while triggering M-Pesa.');
                resetMpesaStatus();
            }
        }

        function startMpesaPoll(checkoutId) {
            cancelMpesaPoll();
            let attempts = 0;
            const statusMsg = document.getElementById('mpesa-status-msg');

            mpesaPollInterval = setInterval(async () => {
                attempts++;
                if (attempts > 30) { // Limit to 60 seconds (2s interval)
                    alert('M-Pesa payment validation timed out. Please enter transaction reference manually if payment went through.');
                    resetMpesaStatus();
                    return;
                }

                try {
                    const response = await fetch(`${window.baseUrl}/controllers/PosController.php?action=check_mpesa_status&checkout_request_id=${encodeURIComponent(checkoutId)}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        if (data.status === 'paid') {
                            statusMsg.innerText = 'Payment Verified Successfully!';
                            document.getElementById('pay-mpesa-ref').value = data.mpesa_receipt;
                            
                            // Highlight verified status
                            const mSpinner = document.getElementById('mpesa-spinner');
                            mSpinner.classList.remove('fa-spinner', 'fa-spin');
                            mSpinner.classList.add('fa-check-circle', 'text-green-500');
                            
                            clearInterval(mpesaPollInterval);
                            alert('M-Pesa payment received! Receipt reference: ' + data.mpesa_receipt);
                            setTimeout(() => {
                                resetMpesaStatus();
                            }, 3000);
                        } else if (data.status === 'failed') {
                            alert('M-Pesa Payment Failed: ' + data.message);
                            resetMpesaStatus();
                        }
                    }
                } catch (err) {
                    console.error('Polling error', err);
                }
            }, 2000);
        }

        function cancelMpesaPoll() {
            if (mpesaPollInterval) {
                clearInterval(mpesaPollInterval);
                mpesaPollInterval = null;
            }
        }

        function resetMpesaStatus() {
            cancelMpesaPoll();
            document.getElementById('btn-stk-push').disabled = false;
            document.getElementById('mpesa-status-box').classList.add('hidden');
            const mSpinner = document.getElementById('mpesa-spinner');
            mSpinner.classList.add('fa-spinner', 'fa-spin');
            mSpinner.classList.remove('fa-check-circle', 'text-green-500');
        }

        // Final checkout submit action
        async function checkoutSale() {
            if (cart.length === 0) {
                alert('Your shopping basket is empty.');
                return;
            }

            const discount = parseFloat(document.getElementById('cart-discount').value) || 0;
            const notes = document.getElementById('cart-notes').value.trim();
            let amountTendered = null;
            let changeGiven = null;
            let mpesaRef = '';

            // Handle payment specific details
            if (paymentMethod === 'cash') {
                amountTendered = parseFloat(document.getElementById('pay-cash-tendered').value);
                if (isNaN(amountTendered) || amountTendered < cartTotal) {
                    alert('Cash Tendered must be equal or greater than the payable total.');
                    return;
                }
                changeGiven = amountTendered - cartTotal;
            } else if (paymentMethod === 'mpesa') {
                mpesaRef = document.getElementById('pay-mpesa-ref').value.trim();
                if (!mpesaRef) {
                    alert('Please enter the M-Pesa Receipt Code reference (e.g. QWE123RTY4).');
                    return;
                }
            } else if (paymentMethod === 'credit') {
                if (!selectedCustomer) {
                    alert('Wholesale customer must be selected to proceed on credit.');
                    return;
                }
                // Verify credit constraints
                const balance = parseFloat(selectedCustomer.outstanding_balance);
                const limit = parseFloat(selectedCustomer.credit_limit);
                if (balance + cartTotal > limit) {
                    alert(`Credit limit exceeded!\nOutstanding: Ksh ${balance.toFixed(2)}\nCredit Limit: Ksh ${limit.toFixed(2)}\nSale Total: Ksh ${cartTotal.toFixed(2)}`);
                    return;
                }
            }

            const btnCheckout = document.getElementById('btn-checkout');
            btnCheckout.disabled = true;
            btnCheckout.innerText = 'SAVING TRANSACTION...';

            const payload = {
                customer_id: selectedCustomer ? selectedCustomer.id : null,
                customer_name: document.getElementById('walkin-name').value.trim(),
                customer_phone: document.getElementById('walkin-phone').value.trim(),
                customer_type: customerType,
                items: cart,
                subtotal: cartSubtotal,
                discount: discount,
                total: cartTotal,
                amount_tendered: amountTendered,
                change_given: changeGiven,
                payment_method: paymentMethod,
                mpesa_ref: mpesaRef,
                notes: notes
            };

            try {
                const response = await fetch(`${window.baseUrl}/controllers/PosController.php?action=checkout`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success) {
                    // Redirect to receipt view
                    alert('Sale completed successfully!');
                    window.location.href = `pos_receipt.php?id=${data.sale_id}`;
                } else {
                    alert('Checkout Failed:\n' + data.message);
                    btnCheckout.disabled = false;
                    btnCheckout.innerHTML = '<i class="fas fa-check-double"></i><span>COMPLETE CHECKOUT</span>';
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred during final check-out.');
                btnCheckout.disabled = false;
                btnCheckout.innerHTML = '<i class="fas fa-check-double"></i><span>COMPLETE CHECKOUT</span>';
            }
        }

        // Live Clock Tick
        function initClock() {
            setInterval(() => {
                const now = new Date();
                document.getElementById('live-clock').innerHTML = `
                    <i class="far fa-clock mr-2"></i> ${now.toLocaleDateString('en-GB')} ${now.toLocaleTimeString('en-US')}
                `;
            }, 1000);
        }

        // Page Init
        window.addEventListener('DOMContentLoaded', () => {
            initClock();
            fetchProducts();
        });
    </script>
</body>
</html>
