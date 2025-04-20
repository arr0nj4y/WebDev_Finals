<?php
require_once 'includes/auth.php';
?>

<header class="bg-white shadow">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <h1 class="text-xl font-bold text-gray-800">ToolTrack</h1>
            <nav class="hidden md:flex space-x-4">
                <a href="index.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                <a href="products.php" class="text-gray-600 hover:text-gray-900">Products</a>
                <a href="sales.php" class="text-gray-600 hover:text-gray-900">Sales</a>
                <?php if (isAdmin()): ?>
                    <a href="admin/suppliers.php" class="text-gray-600 hover:text-gray-900">Suppliers</a>
                    <a href="admin/reports.php" class="text-gray-600 hover:text-gray-900">Reports</a>
                <?php endif; ?>
            </nav>
        </div>
        
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?></span>
            <a href="logout.php" class="text-sm text-red-600 hover:text-red-800">Logout</a>
        </div>
    </div>
</header>