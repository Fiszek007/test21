<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $flightId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM flights WHERE flight_id = ?");
        $stmt->execute([$flightId]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => htmlspecialchars($e->getMessage())]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Flight ID not provided.']);
}
?>
