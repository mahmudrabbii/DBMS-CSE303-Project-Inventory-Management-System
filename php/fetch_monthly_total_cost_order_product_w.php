<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Inventory_Management_System";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to get the total cost for each month from January to December
$sql = "
SELECT 
    MONTHNAME(STR_TO_DATE(months.month, '%m')) AS month, 
    IFNULL(SUM(op.quantity * op.unitPrice), 0) AS totalCost
FROM 
    (SELECT '01' AS month UNION ALL SELECT '02' UNION ALL SELECT '03' UNION ALL SELECT '04' UNION ALL SELECT '05' UNION ALL SELECT '06' UNION ALL SELECT '07' UNION ALL SELECT '08' UNION ALL SELECT '09' UNION ALL SELECT '10' UNION ALL SELECT '11' UNION ALL SELECT '12') AS months
LEFT JOIN 
    PurchaseOrder_T po ON MONTH(po.orderDate) = months.month
LEFT JOIN 
    OrderProduct_T op ON po.purchaseID = op.purchaseID
LEFT JOIN 
    ProcessedProduct_T pp ON op.productID = pp.productID
LEFT JOIN 
    Stakeholder_T sh ON po.DNID = sh.NID
GROUP BY 
    months.month
ORDER BY 
    months.month;
";

// Execute the query
$result = $conn->query($sql);

// Prepare the data array
$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'month' => $row['month'],
            'totalCost' => $row['totalCost']
        ];
    }
} else {
    // Handle any SQL errors
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');
echo json_encode($data);

// Close the connection
$conn->close();
?>
