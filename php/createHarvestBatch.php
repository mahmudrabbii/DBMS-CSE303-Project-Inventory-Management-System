<?php
header('Content-Type: text/plain');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$username = "root"; // Replace with your DB username
$password = ""; // Replace with your DB password
$database = "Inventory_Management_System"; // Replace with your DB name

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper function to log errors
function logError($message) {
    file_put_contents("error_log.txt", date("Y-m-d H:i:s") . " - $message\n", FILE_APPEND);
}

// Validate foreign keys
function validateForeignKey($conn, $table, $column, $value) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Generate IDs with prefix
function generateID($prefix, $length) {
    $uniquePart = str_pad(mt_rand(61, pow(10, $length - strlen($prefix)) - 1), $length - strlen($prefix), "0", STR_PAD_LEFT);
    return $prefix . $uniquePart;
}

// Collect form data
$type = strtolower(trim($_POST['material_category'] ?? '')); // Type from the form
$materialName = $_POST['material_name'] ?? '';
$qualityGrade = $_POST['quality_grade'] ?? '';
$issueDate = $_POST['product_isssue_date'] ?? '';
$expirationDate = $_POST['product_expiration_date'] ?? '';
$farmID = $_POST['farm_id'] ?? '';
$zoneID = $_POST['zone_id'] ?? '';
$quantityUnit = $_POST['product_quantity_unit'] ?? '';
$commodityManagerID = $_POST['commodity_manager_id'] ?? '';
$OCID = $_POST['OC_id'] ?? '';

// Validate required fields
if (empty($type) || empty($materialName) || empty($qualityGrade) || empty($issueDate) || empty($expirationDate)) {
    die("Error: Required fields are missing.");
}

// Type prefixes
$typePrefixes = [
    "plant" => "HPL",
    "fruit" => "HFR",
    "vegetable" => "HVE",
    "grain" => "HGR",
    "herb" => "HER",
];

// Validate type and set prefix
if (!array_key_exists($type, $typePrefixes)) {
    die("Error: Invalid type.");
}
$prefix = $typePrefixes[$type];

// Validate foreign keys
if (!validateForeignKey($conn, "ZoneSensor_T", "zoneID", $zoneID)) {
    die("Error: zoneID $zoneID does not exist in ZoneSensor_T.");
}
if (!validateForeignKey($conn, "Farm_T", "farmID", $farmID)) {
    die("Error: farmID $farmID does not exist in Farm_T.");
}
if (!validateForeignKey($conn, "OptimumCondition_T", "OCID", $OCID)) {
    die("Error: OCID $OCID does not exist in OptimumCondition_T.");
}

// Generate IDs
$batchBarCode = generateID($prefix, 10); // CHAR(10)
$harvestBatchBarCode = $batchBarCode; // Same ID for Batch and HarvestBatch
$harvestMaterialBarCode = generateID($prefix, 7); // CHAR(7)

// Insert into Batch_T
$sqlBatch = "
    INSERT INTO Batch_T (barCode, zoneID, qualityGrade, issuingDate, expirationDate, quantity)
    VALUES (?, ?, ?, ?, ?, ?)
";
$stmtBatch = $conn->prepare($sqlBatch);
if (!$stmtBatch) {
    logError("SQL Preparation Error for Batch_T: " . $conn->error);
    die("Error preparing Batch_T statement.");
}
$stmtBatch->bind_param("sssssi", $batchBarCode, $zoneID, $qualityGrade, $issueDate, $expirationDate, $quantityUnit);
if (!$stmtBatch->execute()) {
    logError("SQL Execution Error for Batch_T: " . $stmtBatch->error);
    die("Error inserting into Batch_T.");
}
$stmtBatch->close();

// Insert into HarvestMaterial_T
$sqlHarvestMaterial = "
    INSERT INTO HarvestMaterial_T (materialID, CMGNID, OCID, name, type)
    VALUES (?, ?, ?, ?, ?)
";
$stmtHarvestMaterial = $conn->prepare($sqlHarvestMaterial);
if (!$stmtHarvestMaterial) {
    logError("SQL Preparation Error for HarvestMaterial_T: " . $conn->error);
    die("Error preparing HarvestMaterial_T statement.");
}
$stmtHarvestMaterial->bind_param("sssss", $harvestMaterialBarCode, $commodityManagerID, $OCID, $materialName, $type);
if (!$stmtHarvestMaterial->execute()) {
    logError("SQL Execution Error for HarvestMaterial_T: " . $stmtHarvestMaterial->error);
    die("Error inserting into HarvestMaterial_T.");
}
$stmtHarvestMaterial->close();

// Insert into HarvestBatch_T
$sqlHarvestBatch = "
    INSERT INTO HarvestBatch_T (HbarCode, farmID, materialID)
    VALUES (?, ?, ?)
";
$stmtHarvestBatch = $conn->prepare($sqlHarvestBatch);
if (!$stmtHarvestBatch) {
    logError("SQL Preparation Error for HarvestBatch_T: " . $conn->error);
    die("Error preparing HarvestBatch_T statement.");
}
$stmtHarvestBatch->bind_param("sss", $harvestBatchBarCode, $farmID, $harvestMaterialBarCode);
if (!$stmtHarvestBatch->execute()) {
    logError("SQL Execution Error for HarvestBatch_T: " . $stmtHarvestBatch->error);
    die("Error inserting into HarvestBatch_T.");
}
$stmtHarvestBatch->close();

echo json_encode([
    "success" => true,
    "batchBarCode" => $batchBarCode,
    "harvestMaterialBarCode" => $harvestMaterialBarCode,
]);

$conn->close();
?>
