Inventory_Management_System
<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$database = "Inventory_Management_System";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$nid = $_POST['nid'];
$name = $_POST['name'];
$gender = $_POST['gender'];
$phone = $_POST['phone_number'];
$email = $_POST['email'];
$city = $_POST['city'];
$thana = $_POST['thana'];
$zip_code = $_POST['zip_code'] ?? NULL; // Optional
$license_number = $_POST['license_number'];
$initials = $_POST['initials'];
$location_id = generateLocationID($city, $thana, $conn); // Custom function to find or insert location

// Validate required fields
if (empty($nid) || empty($name) || empty($gender) || empty($phone) || empty($email) || empty($city) || empty($thana) || empty($license_number)) {
    die("All required fields must be filled out.");
}

// Split full name into first and last names (if possible)
$name_parts = explode(" ", $name);
$first_name = $name_parts[0];
$last_name = isset($name_parts[1]) ? $name_parts[1] : "";

// Start database transaction
$conn->begin_transaction();
try {
    // Insert into Stakeholder_T
    $stmt = $conn->prepare("INSERT INTO Stakeholder_T (NID, locationID, firstName, lastName, gender, initials, registrationDate, stakeholderType) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 'Distributor')");
    $stmt->bind_param("ssssss", $nid, $location_id, $first_name, $last_name, $gender, $initials);
    $stmt->execute();

    // Insert into Distributor_T
    $stmt = $conn->prepare("INSERT INTO Distributor_T (DNID, license) VALUES (?, ?)");
    $stmt->bind_param("ss", $nid, $license_number);
    $stmt->execute();

    // Insert into StakeholderEmail_T
    $stmt = $conn->prepare("INSERT INTO StakeholderEmail_T (stakeholderID, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $nid, $email);
    $stmt->execute();

    // Insert into StakeholderPhone_T
    $stmt = $conn->prepare("INSERT INTO StakeholderPhone_T (stakeholderID, phoneNumber) VALUES (?, ?)");
    $stmt->bind_param("ss", $nid, $phone);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    echo "Distributor added successfully!";
} catch (Exception $e) {
    $conn->rollback();
    die("Error occurred: " . $e->getMessage());
}

// Close connection
$conn->close();

// Helper function to generate or find LocationID
function generateLocationID($city, $thana, $conn) {
    // Check if location already exists
    $stmt = $conn->prepare("SELECT locationID FROM Location_T WHERE city = ? AND thana = ?");
    $stmt->bind_param("ss", $city, $thana);
    $stmt->execute();
    $stmt->bind_result($location_id);
    if ($stmt->fetch()) {
        return $location_id; // Return existing locationID
    }
    $stmt->close();

    // If not found, insert new location
    $location_id = uniqid("LOC"); // Generate unique ID
    $stmt = $conn->prepare("INSERT INTO Location_T (locationID, city, thana) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $location_id, $city, $thana);
    $stmt->execute();
    return $location_id;
}
?>
