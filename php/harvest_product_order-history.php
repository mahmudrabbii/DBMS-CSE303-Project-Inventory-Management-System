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

// SQL query to fetch harvest product details
$sql = "
SELECT 
    b.barCode AS BarCode_Batch,
    hm.name AS Harvest_Material_Name,
    hm.type AS Harvest_Type,
    b.qualityGrade AS Batch_Quality_Grade,
    b.issuingDate AS Harvest_Date,
    b.expirationDate AS Batch_Expiration_Date,
    b.quantity AS Batch_Quantity,
    CASE 
        WHEN b.expirationDate < CURDATE() THEN 'Expired'
        ELSE 'Good'
    END AS Status
FROM Batch_T b
JOIN HarvestBatch_T hb ON b.barCode = hb.HbarCode
JOIN HarvestMaterial_T hm ON hb.materialID = hm.materialID;
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
