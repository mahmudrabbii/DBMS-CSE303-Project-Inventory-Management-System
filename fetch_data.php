<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Inventory_Management_System";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get date range from query parameters
$fromDate = $_GET['fromDate'] ;
$toDate = $_GET['toDate'] ;

if (!$fromDate || !$toDate) {
    echo json_encode(['error' => 'Invalid date range']);
    exit;
}

// Query to fetch total quantities grouped by material type
$sql = "SELECT 
            hm.type AS materialType,
            SUM(b.quantity) AS totalQuantity
        FROM 
            HarvestMaterial_T hm
        JOIN 
            HarvestBatch_T hb ON hm.materialID = hb.materialID
        JOIN 
            Batch_T b ON hb.HbarCode = b.barCode
        WHERE 
            b.issuingDate BETWEEN ? AND ?
        GROUP BY 
            hm.type";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $fromDate, $toDate);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$conn->close();
?>
