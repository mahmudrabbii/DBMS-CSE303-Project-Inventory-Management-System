<?php
// Set response header
header('Content-Type: application/json');

// Database connection details
$host = "localhost";
$user = "root";
$password = "";
$dbname = "Inventory_Management_System";

try {
    // Establish PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL query
    $sql = "
        SELECT 
            b.barCode AS `Bar Code`,
            b.zoneID AS `Zone ID`,
            pp.name AS `Product Name`,
            pp.productType AS `Type`,
            b.issuingDate AS `Harvest Date`,
            b.expirationDate AS `Expiration Date`,
            b.quantity AS `Quantity`,
            b.qualityGrade AS `Quality Grade`
        FROM 
            Spoilage_T s
        JOIN 
            ProcessedProduct_T pp ON s.productID = pp.productID
        JOIN 
            Batch_T b ON s.PbarCode = b.barCode;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch results as associative array
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    echo json_encode($result);

} catch (PDOException $e) {
    // Handle errors and return as JSON
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
?>
