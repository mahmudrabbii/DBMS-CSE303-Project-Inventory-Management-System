<?php
// Database connection parameters
$host = "localhost";
$username = "root"; // Replace with your DB username
$password = ""; // Replace with your DB password
$database = "Inventory_Management_System"; // Replace with your DB name

// Establish connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// SQL query to fetch order history details
$sql = "
SELECT 
    po.purchaseID AS 'Product ID',
    po.orderDate AS 'Order Date',
    CONCAT(sh.firstName, ' ', sh.lastName) AS 'Distributor Name',
    pp.name AS 'Product Name',
    op.quantity AS 'Quantity Ordered',
    op.unitPrice AS 'Unit Price',
    (op.quantity * op.unitPrice) AS 'Total Cost',
    po.estimatedDeliveryDate AS 'Delivery Date'
FROM 
    PurchaseOrder_T po
JOIN 
    OrderProduct_T op ON po.purchaseID = op.purchaseID
JOIN 
    ProcessedProduct_T pp ON op.productID = pp.productID
JOIN 
    Stakeholder_T sh ON po.DNID = sh.NID;
";

// Execute query
$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

// Fetch results
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);

// Close connection
$conn->close();
?>
