<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "Inventory_Management_System";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

// Retrieve JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["items"]) || empty($data["items"])) {
    die(json_encode(["success" => false, "message" => "No items found in the request."]));
}

// Generate a unique purchase ID
$result = $conn->query("SELECT purchaseID FROM PurchaseOrder_T ORDER BY purchaseID DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastPurchaseID = $row['purchaseID'];
    $lastIDNumber = intval(substr($lastPurchaseID, 3));
    $newIDNumber = $lastIDNumber + 1;
} else {
    $newIDNumber = 61; // Start with PUR0061 if no records exist
}
$purchaseID = "PUR" . str_pad($newIDNumber, 4, "0", STR_PAD_LEFT);

// Static values for testing purposes
$distributorID = "D-00001"; // Example distributor ID, replace with a dynamic value if necessary
$orderDate = date("Y-m-d");
$estimatedDeliveryDate = date("Y-m-d", strtotime("+7 days"));
$paymentMethod = "Credit Card";

// Insert into PurchaseOrder_T table
$sqlPurchase = "INSERT INTO PurchaseOrder_T (purchaseID, DNID, orderDate, estimatedDeliveryDate, paymentMethod)
VALUES ('$purchaseID', '$distributorID', '$orderDate', '$estimatedDeliveryDate', '$paymentMethod')";

if (!$conn->query($sqlPurchase)) {
    die(json_encode(["success" => false, "message" => "Error inserting into PurchaseOrder_T: " . $conn->error]));
}

// Insert into OrderProduct_T table for each item
foreach ($data["items"] as $item) {
    $productName = $item["productName"];
    $productType = $item["productType"];
    $quantity = $item["quantity"];
    $unitPrice = $item["unitPrice"];

    // Fetch the product ID based on the product name and type
    $result = $conn->query("SELECT productID FROM ProcessedProduct_T WHERE name = '$productName' AND productType = '$productType'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productID = $row["productID"];

        // Insert into OrderProduct_T table
        $sqlOrderProduct = "INSERT INTO OrderProduct_T (purchaseID, productID, unitPrice, quantity)
        VALUES ('$purchaseID', '$productID', '$unitPrice', '$quantity')";

        if (!$conn->query($sqlOrderProduct)) {
            die(json_encode(["success" => false, "message" => "Error inserting into OrderProduct_T: " . $conn->error]));
        }
    } else {
        die(json_encode(["success" => false, "message" => "Product not found: $productName, $productType"]));
    }
}

// Respond with success if everything is inserted correctly
echo json_encode(["success" => true, "message" => "Order placed successfully"]);
?>
