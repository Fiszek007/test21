<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $flightNumber = $_POST['flight_number'];
    $departureAirport = $_POST['departure_airport'];
    $arrivalAirport = $_POST['arrival_airport'];
    $departureTime = $_POST['departure_time'];
    $arrivalTime = $_POST['arrival_time'];
    $airlineId = $_POST['airline_id'];
    $aircraftType = $_POST['aircraft_type'];

    if (empty($flightNumber) || empty($departureAirport) || empty($arrivalAirport) || empty($departureTime) || empty($arrivalTime) || empty($airlineId)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO flights (flight_number, departure_airport, arrival_airport, departure_time, arrival_time, airline_id, aircraft_type) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$flightNumber, $departureAirport, $arrivalAirport, $departureTime, $arrivalTime, $airlineId, $aircraftType]);
        echo json_encode(['status' => 'success', 'message' => 'Flight added successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . htmlspecialchars($e->getMessage())]);
    }
}
?>