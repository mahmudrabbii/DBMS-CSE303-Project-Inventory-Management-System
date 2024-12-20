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

// Fetch total quantity by type
$sql = "SELECT 
            hm.type AS materialType,
            SUM(b.quantity) AS totalQuantity
        FROM 
            HarvestMaterial_T hm
        JOIN 
            HarvestBatch_T hb 
        ON 
            hm.materialID = hb.materialID
        JOIN 
            Batch_T b
        ON 
            hb.HbarCode = b.barCode
        GROUP BY 
            hm.type";

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);

$conn->close();
?>
