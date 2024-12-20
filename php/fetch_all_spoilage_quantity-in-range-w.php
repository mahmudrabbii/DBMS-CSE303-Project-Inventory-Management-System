<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Inventory_Management_System";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get date range from the request
$fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : null;
$toDate = isset($_GET['toDate']) ? $_GET['toDate'] : null;

// SQL Query
$sql = "
SELECT 
    PP.productType AS materialType,
    COALESCE(SUM(S.quantity), 0) AS totalQuantity
FROM 
    ProcessedProduct_T PP
LEFT JOIN 
    Spoilage_T S
ON 
    S.productID = PP.productID
AND 
    S.date BETWEEN ? AND ?
GROUP BY 
    PP.productType;

";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss",$fromDate, $toDate);
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch data as an array
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Close connections
$stmt->close();
$conn->close();

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
