<?php
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function getSalesReport($startDate, $endDate) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT 
                           DATE(sale_date) as sale_day, 
                           SUM(total_amount) as daily_total,
                           COUNT(id) as transactions
                           FROM sales 
                           WHERE sale_date BETWEEN ? AND ?
                           GROUP BY DATE(sale_date)
                           ORDER BY sale_date");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $report = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
    
    return $report;
}

function recordSale($productId, $quantity, $userId) {
    $conn = getDBConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get product details
        $stmt = $conn->prepare("SELECT price, stock_quantity FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product || $product['stock_quantity'] < $quantity) {
            throw new Exception("Insufficient stock or invalid product");
        }
        
        $total = $product['price'] * $quantity;
        
        // Record sale
        $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, unit_price, total_amount, user_id) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiddi", $productId, $quantity, $product['price'], $total, $userId);
        $stmt->execute();
        
        // Update stock
        $newQuantity = $product['stock_quantity'] - $quantity;
        $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $newQuantity, $productId);
        $stmt->execute();
        
        // Record stock movement
        $stmt = $conn->prepare("INSERT INTO stock_movements (product_id, quantity, movement_type, user_id) 
                               VALUES (?, ?, 'sale', ?)");
        $stmt->bind_param("iii", $productId, $quantity, $userId);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Sale recording failed: " . $e->getMessage());
        return false;
    } finally {
        $conn->close();
    }
}
?>