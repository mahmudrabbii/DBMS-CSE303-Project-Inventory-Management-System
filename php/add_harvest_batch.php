<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = "";     // Default password for XAMPP
$dbname = "Inventory_Management_System"; // Replace with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate random IDs
function generateRandomID($length = 7) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomID = '';
    for ($i = 0; $i < $length; $i++) {
        $randomID .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomID;
}

// Fetch form data
$name = $_POST['name'];
$type = $_POST['type'];
$quantity = $_POST['quantity'];
$qualityGrade = $_POST['qualityGrade'];
$harvestDate = $_POST['harvestDate'];
$expirationDate = $_POST['expirationDate'];
$farmID =  $_POST['farmID'];

// Generate IDs

$ocid = 'OCID001'; //fetch from  preferd type
$cmgnID = 'CMG001';// fetch the commiidity user

$materialID = generateRandomID();
$batchBarCode = generateRandomID(10);




// Convert expirationDate to yyyy-mm-dd
$expirationDateObject = DateTime::createFromFormat('d/m/y', $expirationDate);
if ($expirationDateObject) {
    $formattedExpirationDate = $expirationDateObject->format('Y-m-d'); // Converts to 'yyyy-mm-dd'
} else {
    die("Invalid expiration date format. Please enter the date as dd/mm/yy.");
}

// Convert harvestDate to yyyy-mm-dd
$harvestDateObject = DateTime::createFromFormat('d/m/y', $harvestDate);
if ($harvestDateObject) {
    $formattedHarvestDate = $harvestDateObject->format('Y-m-d'); // Converts to 'yyyy-mm-dd'
} else {
    die("Invalid harvest date format. Please enter the date as dd/mm/yy.");
}








// Step 6: Insert into HarvestMaterial_T
$sqlHarvestMaterial = "INSERT INTO HarvestMaterial_T (materialID, CMGNID, OCID, `name`, `type`)
                       VALUES ('$materialID', '$cmgnID', '$ocid', '$name', '$type')";

if ($conn->query($sqlHarvestMaterial) === TRUE) {
    echo "Harvest material added successfully.<br>";
} else {
    die("Error inserting into HarvestMaterial_T: " . $conn->error . "<br>");
}



// Step 7: Insert into Batch_T
$sqlBatch = "INSERT INTO Batch_T (barCode, qualityGrade, issuingDate, expirationDate, quantity)
             VALUES ('$batchBarCode', '$qualityGrade', '$formattedHarvestDate', '$formattedExpirationDate', $quantity)";

if ($conn->query($sqlBatch) === TRUE) {
    echo "Batch added successfully.<br>";
} else {
    die("Error inserting into Batch_T: " . $conn->error . "<br>");
}



// Step 8: Insert into HarvestBatch_T
$sqlHarvestBatch = "INSERT INTO HarvestBatch_T (HbarCode, farmID, materialID)
                    VALUES ('$batchBarCode', '$farmID', '$materialID')";

if ($conn->query($sqlHarvestBatch) === TRUE) {
    echo "Harvest batch added successfully.<br>";
} else {
    die("Error inserting into HarvestBatch_T: " . $conn->error . "<br>");
}

// Close connection
$conn->close();
?>
