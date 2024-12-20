<?php
// Database connection configuration
$host = 'localhost';
$db = 'Inventory_Management_System';
$user = 'root'; // Replace with your DB username
$password = ''; // Replace with your DB password

// Create connection
$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch data
$sql = "SELECT 
            pp.name AS Product_Name,
            pp.productType AS Product_Type,
            ph.price AS Price
        FROM 
            ProcessedProduct_T pp
        JOIN 
            PriceHistory_T ph
        ON 
            pp.productID = ph.productID";

$result = $conn->query($sql);

// Prepare data in JSON format
if ($result->num_rows > 0) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

// Close connection
$conn->close();
?>
