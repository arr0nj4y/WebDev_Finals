<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$conn = getDBConnection();

// Get stats for dashboard
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalSalesToday = $conn->query("SELECT SUM(total_amount) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['total'];
$lowStockItems = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= min_stock_level")->fetch_assoc()['count'];

// Get recent sales
$recentSales = $conn->query("SELECT s.*, p.name as product_name, u.username 
                            FROM sales s 
                            JOIN products p ON s.product_id = p.id 
                            JOIN users u ON s.user_id = u.id 
                            ORDER BY s.sale_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToolTrack - Dashboard</title>
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Total Products</h3>
                <p class="text-2xl font-bold text-gray-800"><?= $totalProducts ?></p>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Today's Sales</h3>
                <p class="text-2xl font-bold text-gray-800"><?= formatCurrency($totalSalesToday ?? 0) ?></p>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Low Stock Items</h3>
                <p class="text-2xl font-bold text-gray-800"><?= $lowStockItems ?></p>
            </div>
        </div>
        
        <!-- Recent Sales and Low Stock Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Sales -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Sales</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sold By</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentSales as $sale): ?>
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($sale['product_name']) ?></td>
                                <td class="px-4 py-2 whitespace-nowrap"><?= $sale['quantity'] ?></td>
                                <td class="px-4 py-2 whitespace-nowrap"><?= formatCurrency($sale['total_amount']) ?></td>
                                <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($sale['username']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Low Stock Alerts -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Low Stock Alerts</h2>
                <?php 
                $lowStockProducts = getLowStockProducts();
                if (count($lowStockProducts) > 0): ?>
                    <div class="space-y-2">
                        <?php foreach ($lowStockProducts as $product): ?>
                            <div class="p-2 bg-yellow-50 border-l-4 border-yellow-400">
                                <div class="flex justify-between">
                                    <span class="font-medium"><?= htmlspecialchars($product['name']) ?></span>
                                    <span class="text-sm">Stock: <?= $product['stock_quantity'] ?></span>
                                </div>
                                <div class="text-sm text-gray-600">Category: <?= htmlspecialchars($product['category_name']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No low stock items</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isAdmin()): ?>
        <!-- Admin-only content -->
        <div class="mt-6 bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="flex flex-wrap gap-2">
                <a href="admin/products/add.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Product</a>
                <a href="admin/suppliers/add.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add Supplier</a>
                <a href="admin/reports.php" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Generate Report</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>