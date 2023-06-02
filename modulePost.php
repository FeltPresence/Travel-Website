$<?php
// Start the session
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Set the HTTP response status code to 401 Unauthorized
    http_response_code(401);
    die("Unauthorized access.");
}

// Get the values of 'smokeLevel' and 'temperature' from the HTTP POST request
$smoke_level = isset($_POST["smokeLevel"]) ? filter_var($_POST["smokeLevel"], FILTER_SANITIZE_NUMBER_INT) : 0;
$temperature = isset($_POST["temperature"]) ? filter_var($_POST["temperature"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

// Check if the data is coming from the SIM800L module using a secret key
$secret_key = "my_secret_key";
if (!isset($_POST["key"]) || $_POST["key"] !== $secret_key) {
    die("Unauthorized access.");
}

// Debugging statements
var_dump($_POST);
var_dump($secret_key);

// Sanitize and validate input data
if ($smoke_level < 0 || $smoke_level > 100) {
    die("Invalid smoke level.");
}

if ($temperature < -273.15 || $temperature > 1000) {
    die("Invalid temperature.");
}

// Use parameterized queries to insert data into the database
$mysqli = new mysqli("localhost", "root", "", "sensordata_db");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$stmt = $mysqli->prepare("INSERT INTO sensordataform(smoke_level, temperature) VALUES (?, ?)");
$stmt->bind_param("ii", $smoke_level, $temperature);

// Execute the query and check for errors
if (!$stmt->execute()) {
    die("Error: " . $stmt->error);
}

// Close the statement and database connection
$stmt->close();
$mysqli->close();

// Return a success message to the client
echo "Data inserted successfully.";
?>