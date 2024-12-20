<?php
header('Content-Type: text/plain');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "Inventory_Management_System";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Log incoming data
$logFile = "/opt/lampp/htdocs/DBMS-CSE303-Project-Inventory-Management-System-main/php/php_debug.log";
try {
    $logData = "POST Data: " . print_r($_POST, true) . PHP_EOL;
    file_put_contents($logFile, $logData, FILE_APPEND);
} catch (Exception $e) {
    echo "Failed to log debug data: " . $e->getMessage();
}

// Extract form data
$orderId = $_POST["orderId"] ?? null;
$shippingDate = $_POST["shippingDate"] ?? null;
$transportMethod = $_POST["transportMethod"] ?? null;
$arrivalDate = $_POST["arrivalDate"] ?? null;
$shippingCost = $_POST["shippingCost"] ?? null;
$inspectionDate = $_POST["inspectionDate"] ?? null;
$regulatoryClearance = $_POST["regulatoryClearance"] ?? null;
$remarks = $_POST["remarks"] ?? null;

// Default PbarCode
$pbarCode = "PFR010001";

// Validate required fields
if (!$orderId || !$shippingDate || !$transportMethod || !$arrivalDate || !$shippingCost || !$inspectionDate || !$regulatoryClearance) {
    die("Missing required fields");
}

// Validate `regulatoryClearance` length
if (strlen($regulatoryClearance) > 20) {
    die("Value for 'regulatoryClearance' exceeds allowed length");
}

// Generate unique tracking number
// Generate a 10-digit unique tracking number
$trackingNumber = str_pad(random_int(1, 9999999999), 10, "0", STR_PAD_LEFT);

$regulatoryClearance= "clr";

// Insert data into Shipment_T
$sql = "
    INSERT INTO Shipment_T (
        trackingNumber, PbarCode, purchaseID, shippingDate, transportMethod,
        arrivalDate, shippingCost, inspectionDate, regulatoryClearance, remarks
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    file_put_contents($logFile, "SQL Preparation Error: " . $conn->error . PHP_EOL, FILE_APPEND);
    die("Error preparing SQL statement");
}

$stmt->bind_param(
    "ssssssssss",
    $trackingNumber,
    $pbarCode,
    $orderId,
    $shippingDate,
    $transportMethod,
    $arrivalDate,
    $shippingCost,
    $inspectionDate,
    $regulatoryClearance,
    $remarks
);

if ($stmt->execute()) {
    echo "Shipment successfully added";
} else {
    file_put_contents($logFile, "SQL Execution Error: " . $stmt->error . PHP_EOL, FILE_APPEND);
    die("Database Error: " . $stmt->error);
}

$conn->close();
?>
