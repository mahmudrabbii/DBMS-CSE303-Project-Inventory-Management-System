<?php
// Database connection
$servername = "localhost"; // Replace with your server name
$username = "root";        // Replace with your username
$password = "";            // Replace with your password
$dbname = "Inventory_Management_System"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query
$sql = "
SELECT 
    PP.name AS productName,
    SD.zoneID,
    ZS.warehouseID,
    SD.temp,
    SD.humidity
FROM 
    SensorData_T SD
JOIN 
    ZoneSensor_T ZS
ON 
    SD.zoneID = ZS.zoneID
JOIN 
    Batch_T B
ON 
    ZS.zoneID = B.zoneID
JOIN 
    ProcessedProductBatch_T PPB
ON 
    B.barCode = PPB.PbarCode
JOIN 
    Spoilage_T S
ON 
    PPB.PbarCode = S.PbarCode
JOIN 
    ProcessedProduct_T PP
ON 
    S.productID = PP.productID;
";

$result = $conn->query($sql);

// Return data as JSON
$data = [];
if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
} else {
    // Handle SQL errors
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit();
}

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
