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
        'Processed Fruit' AS productType, 
        COALESCE(SUM(CASE WHEN barCode LIKE 'PFR%' THEN quantity ELSE 0 END), 0) AS totalQuantity
    FROM Batch_T
    WHERE issuingDate BETWEEN ? AND ?
    UNION ALL
    SELECT 
        'Processed Grain' AS productType, 
        COALESCE(SUM(CASE WHEN barCode LIKE 'PGR%' THEN quantity ELSE 0 END), 0) AS totalQuantity
    FROM Batch_T
    WHERE issuingDate BETWEEN ? AND ?
    UNION ALL
    SELECT 
        'Processed Herb' AS productType, 
        COALESCE(SUM(CASE WHEN barCode LIKE 'PHE%' THEN quantity ELSE 0 END), 0) AS totalQuantity
    FROM Batch_T
    WHERE issuingDate BETWEEN ? AND ?
    UNION ALL
    SELECT 
        'Processed Plant' AS productType, 
        COALESCE(SUM(CASE WHEN barCode LIKE 'PPL%' THEN quantity ELSE 0 END), 0) AS totalQuantity
    FROM Batch_T
    WHERE issuingDate BETWEEN ? AND ?
    UNION ALL
    SELECT 
        'Processed Vegetable' AS productType, 
        COALESCE(SUM(CASE WHEN barCode LIKE 'PVE%' THEN quantity ELSE 0 END), 0) AS totalQuantity
    FROM Batch_T
    WHERE issuingDate BETWEEN ? AND ?;
";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssss", $fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate);
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
