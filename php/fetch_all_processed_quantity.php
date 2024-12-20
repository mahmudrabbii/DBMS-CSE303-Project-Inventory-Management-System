<?php
// Database connection
$servername = "localhost"; // Update with your database server
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "Inventory_Management_System"; // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch product types and their total quantities
$sql = "
    SELECT LEFT(barCode, 3) AS productType, SUM(quantity) AS totalQuantity
    FROM Batch_T
    WHERE barCode LIKE 'P%'
    GROUP BY productType
";

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = array(
            "productType" => $row["productType"],
            "totalQuantity" => (int)$row["totalQuantity"]
        );
    }
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>

