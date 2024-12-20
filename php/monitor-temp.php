<?php
// Database connection parameters
$host = "localhost";
$username = "root"; // Replace with your DB username
$password = ""; // Replace with your DB password
$database = "Inventory_Management_System"; // Replace with your DB name

// Establish connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// SQL query to fetch relevant data
$sql = "
WITH LatestSensorData AS (
    SELECT 
        s.zoneID,
        MAX(s.timeStamp) AS latestTimeStamp
    FROM SensorData_T s
    GROUP BY s.zoneID
),
LatestSensorDetails AS (
    SELECT 
        s.zoneID,
        lsd.latestTimeStamp,
        s.temp AS latestTemp,
        s.humidity AS latestHumidity
    FROM SensorData_T s
    JOIN LatestSensorData lsd ON s.zoneID = lsd.zoneID AND s.timeStamp = lsd.latestTimeStamp
),
ZoneMaterialConditions AS (
    SELECT 
        b.zoneID,
        w.warehouseID,
        oc.minTemp,
        oc.maxTemp,
        oc.targetHumidity,
        oc.humidityDeviation
    FROM Batch_T b
    JOIN HarvestBatch_T hb ON b.barCode = hb.HbarCode
    JOIN HarvestMaterial_T hm ON hb.materialID = hm.materialID
    JOIN OptimumCondition_T oc ON hm.OCID = oc.OCID
    JOIN ZoneSensor_T z ON b.zoneID = z.zoneID
    JOIN Warehouse_T w ON z.warehouseID = w.warehouseID
),
ZoneAverageConditions AS (
    SELECT 
        zmc.zoneID,
        zmc.warehouseID,
        AVG(zmc.minTemp) AS avgMinTemp,
        AVG(zmc.maxTemp) AS avgMaxTemp,
        AVG(zmc.targetHumidity) AS avgTargetHumidity,
        AVG(zmc.humidityDeviation) AS avgHumidityDeviation
    FROM ZoneMaterialConditions zmc
    GROUP BY zmc.zoneID, zmc.warehouseID
)
SELECT 
    zac.warehouseID,
    zac.zoneID,
    zac.avgMinTemp,
    zac.avgMaxTemp,
    zac.avgTargetHumidity,
    zac.avgHumidityDeviation,
    lsd.latestTimeStamp,
    lsd.latestTemp,
    lsd.latestHumidity
FROM ZoneAverageConditions zac
LEFT JOIN LatestSensorDetails lsd ON zac.zoneID = lsd.zoneID
ORDER BY zac.warehouseID, zac.zoneID;
";

// Execute query
$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

// Fetch results
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);

// Close connection
$conn->close();
?>
