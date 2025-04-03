<?php
include 'db_connection.php';
session_start();

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];

    // Query to fetch flight numbers for the user's bookings
    $stmt = $pdo->prepare("
    SELECT b.booking_date, f.flight_number, f.departure_airport, f.arrival_airport, 
           f.departure_airport, f.arrival_airport, a.name AS airline, b.status 
    FROM bookings b
    JOIN flights f ON b.flight_id = f.flight_id
    JOIN airlines a ON f.airline_id = a.airline_id
    WHERE b.user_id = :user_id
    ORDER BY b.booking_date DESC
");
    $stmt->execute([':user_id' => $user_id]);

    // Fetch all rows
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return results as JSON
    echo json_encode(['status' => 'success', 'data' => $bookings]);
} catch (PDOException $e) {
    // Log the error for debugging
    error_log("Error fetching booking history: " . $e->getMessage(), 3, "error_log.txt");

    // Return an error message as JSON
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch booking history.']);
}
?>
