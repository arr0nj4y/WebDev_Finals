<?php
require_once __DIR__ . '/../config/database.php';

// Basic CRUD operations would go here
// Example for products:

function getAllProducts($search = '', $category = null) {
    $conn = getDBConnection();
    $query = "SELECT p.*, c.name as category_name, s.name as supplier_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN suppliers s ON p.supplier_id = s.id 
              WHERE p.name LIKE ?";
    
    $params = ["%$search%"];
    $types = "s";
    
    if ($category) {
        $query .= " AND p.category_id = ?";
        $params[] = $category;
        $types .= "i";
    }
    
    $query .= " ORDER BY p.name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
    
    return $products;
}

function getLowStockProducts($threshold = 5) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.stock_quantity <= p.min_stock_level 
                           ORDER BY p.stock_quantity ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
    
    return $products;
}

// Similar functions for other tables (users, sales, suppliers, etc.)
?>