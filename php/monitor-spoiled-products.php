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

// SQL query to fetch spoiled products
$sql = "
SELECT 
    sp.spoilageID AS Spoilage_ID,
    sp.PbarCode AS Product_BarCode,
    pp.name AS Product_Name,
    pp.productType AS Product_Type,
    sp.quantity AS Spoiled_Quantity,
    sp.date AS Spoilage_Date,
    sp.actionTaken AS Action_Taken,
    sp.lossValue AS Loss_Value,
    b.qualityGrade AS Quality_Grade,
    b.issuingDate AS Harvest_Date,
    b.expirationDate AS Expiration_Date,
    w.warehouseName AS Warehouse_Name,
    z.zoneName AS Zone_Name
FROM Spoilage_T sp
JOIN ProcessedProduct_T pp ON sp.productID = pp.productID
JOIN ProcessedProductBatch_T pb ON sp.PbarCode = pb.PbarCode
JOIN Batch_T b ON pb.PbarCode = b.barCode
JOIN ZoneSensor_T z ON b.zoneID = z.zoneID
JOIN Warehouse_T w ON z.warehouseID = w.warehouseID
ORDER BY sp.date DESC;
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
