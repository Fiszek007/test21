<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $flightId = $_GET['id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Copy flight data to archive table
        $stmt = $pdo->prepare("
            INSERT INTO flights_archive (flight_id, flight_number, departure_airport, arrival_airport, departure_time, arrival_time, airline_id, aircraft_type)
            SELECT flight_id, flight_number, departure_airport, arrival_airport, departure_time, arrival_time, airline_id, aircraft_type
            FROM flights
            WHERE flight_id = ?
        ");
        $stmt->execute([$flightId]);

        // Mark flight as deleted
        $stmt = $pdo->prepare("UPDATE flights SET deleted = 1 WHERE flight_id = ?");
        $stmt->execute([$flightId]);

        // Commit transaction
        $pdo->commit();

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        // Rollback transaction in case of error
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => htmlspecialchars($e->getMessage())]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Flight ID not provided.']);
}
?>
