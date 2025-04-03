<?php
include 'db_connection.php';

session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

try {
    $user_id = $_SESSION['user_id']; // Logged-in user's ID
    $flight_id = $_POST['flight_id'] ?? null; // Assuming flight_id is sent via POST
    $ticket_price = $_POST['ticket_price'] ?? null;
    $payment_method = $_POST['payment_method'] ?? 'Card'; // Default to 'Card'
    $num_suitcases = (int)($_POST['num_suitcases'] ?? 0); // Default to 0 suitcases
    $weight_per_suitcase = (int)($_POST['weight_per_suitcase'] ?? 0); // Default to 0 weight

    if (empty($flight_id) || empty($ticket_price)) {
        die("Error: Missing required fields.");
    }

    // Validate flight_id
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM flights WHERE flight_id = :flight_id");
    $stmt->execute([':flight_id' => $flight_id]);
    if ($stmt->fetchColumn() == 0) {
        die("Error: The selected flight does not exist.");
    }

    // Calculate luggage cost
    $luggage_cost = 0;
    if ($num_suitcases === 1) {
        $luggage_cost = 20; // $20 for 1 suitcase
    } elseif ($num_suitcases === 2) {
        $luggage_cost = 30; // $30 for 2 suitcases
    }

    // Calculate total cost
    $total_cost = $ticket_price + $luggage_cost;

    // Start transaction
    $pdo->beginTransaction();

    // Insert into `bookings` table
    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, booking_date, flight_id, status) 
        VALUES (:user_id, NOW(), :flight_id, 'Confirmed')
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':flight_id' => $flight_id,
    ]);
    $booking_id = $pdo->lastInsertId();

    // Insert into `tickets` table
    $stmt = $pdo->prepare("
        INSERT INTO tickets (booking_id, flight_id, seat_number, ticket_class) 
        VALUES (:booking_id, :flight_id, :seat_number, :ticket_class)
    ");
    $stmt->execute([
        ':booking_id' => $booking_id,
        ':flight_id' => $flight_id,
        ':seat_number' => 'A1', // Placeholder seat number
        ':ticket_class' => 'Economy', // Default class
    ]);
    $ticket_id = $pdo->lastInsertId();

    // Insert luggage if applicable
    if ($num_suitcases > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO luggage (ticket_id, weight, type) 
            VALUES (:ticket_id, :weight, :type)
        ");
        $stmt->execute([
            ':ticket_id' => $ticket_id,
            ':weight' => $weight_per_suitcase * $num_suitcases,
            ':type' => 'Checked-in',
        ]);
    }

    // Insert payment
    $stmt = $pdo->prepare("
        INSERT INTO payments (booking_id, amount, payment_date, status) 
        VALUES (:booking_id, :amount, NOW(), 'Completed')
    ");
    $stmt->execute([
        ':booking_id' => $booking_id,
        ':amount' => $total_cost,
    ]);

    // Commit transaction
    $pdo->commit();

    // Success Message
    echo "<script>alert('Payment Successful! Booking confirmed. Total cost: $$total_cost');</script>";
    echo "<script>window.location.href = 'welcome.php';</script>";
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    die("<p>Error processing payment: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
