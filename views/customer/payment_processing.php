<?php
session_start();
require_once '../../config/database.php';
require_once '../../controllers/OrderController.php';

if (!isset($_GET['order_id']) || !isset($_SESSION['checkout_data'])) {
    header('Location: checkout.php');
    exit();
}

$order_id = $_GET['order_id'];
$checkout_data = $_SESSION['checkout_data'];

$orderController = new OrderController();
$payment_status = $orderController->getOrderPaymentStatus($order_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - Lake Victoria Tilapia Depot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include '../../views/includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="mb-6">
                    <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Processing Your Payment</h1>
                    <p class="text-gray-600">Please complete the payment on your phone</p>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-2 gap-4 text-left mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Order ID</p>
                            <p class="font-semibold">#<?php echo $order_id; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Amount</p>
                            <p class="font-semibold">KSh <?php echo number_format($checkout_data['amount'], 2); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($checkout_data['phone']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-semibold text-yellow-600">Waiting for payment</p>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                        <h4 class="font-semibold text-yellow-800 mb-2">
                            <i class="fas fa-mobile-alt mr-2"></i>Check Your Phone
                        </h4>
                        <p class="text-sm text-yellow-700">
                            We've sent an M-Pesa prompt to <?php echo htmlspecialchars($checkout_data['phone']); ?>.
                            Please enter your M-Pesa PIN to complete the payment.
                        </p>
                    </div>
                </div>

                <div class="flex justify-center space-x-4">
                    <button onclick="checkPaymentStatus()"
                        class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-sync-alt mr-2"></i>Check Status
                    </button>
                    <a href="order_success.php?id=<?php echo $order_id; ?>"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 hidden"
                        id="successBtn">
                        <i class="fas fa-check mr-2"></i>Continue
                    </a>
                </div>

                <div id="statusMessage" class="mt-4"></div>
            </div>
        </div>
    </div>

    <script>
        let checkCount = 0;
        const maxChecks = 30; // 2.5 minutes at 5-second intervals

        function checkPaymentStatus() {
            checkCount++;
            const statusMessage = document.getElementById('statusMessage');
            const successBtn = document.getElementById('successBtn');

            statusMessage.innerHTML = '<div class="text-blue-600"><i class="fas fa-spinner fa-spin mr-2"></i>Checking payment status...</div>';

            fetch('check_payment_status.php?order_id=<?php echo $order_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusMessage.innerHTML = '<div class="text-green-600"><i class="fas fa-check-circle mr-2"></i>Payment successful! Redirecting...</div>';
                        successBtn.classList.remove('hidden');
                        setTimeout(() => {
                            window.location.href = 'order_success.php?id=<?php echo $order_id; ?>';
                        }, 2000);
                    } else if (checkCount < maxChecks) {
                        statusMessage.innerHTML = '<div class="text-yellow-600"><i class="fas fa-clock mr-2"></i>Still waiting for payment... (' + checkCount + '/' + maxChecks + ')</div>';
                        setTimeout(checkPaymentStatus, 5000); // Check again in 5 seconds
                    } else {
                        statusMessage.innerHTML = '<div class="text-red-600"><i class="fas fa-times-circle mr-2"></i>Payment timeout. Please try again.</div>';
                    }
                })
                .catch(error => {
                    statusMessage.innerHTML = '<div class="text-red-600"><i class="fas fa-exclamation-triangle mr-2"></i>Error checking status. Please try again.</div>';
                });
        }

        // Start checking automatically
        setTimeout(checkPaymentStatus, 3000);
    </script>
</body>

</html>