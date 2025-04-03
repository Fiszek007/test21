<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departure_city_id = $_POST['departure_airport'] ?? null;
    $arrival_city_id = $_POST['arrival_airport'] ?? null;
    $departure_date = $_POST['departure_date'] ?? null;
    $return_date = $_POST['return_date'] ?? null;
    $travel_type = $_POST['travel_type'] ?? 'one_way';
    $sort_order = $_POST['sort_order'] ?? 'asc';

    // Validate required fields
    if (empty($departure_city_id) || empty($arrival_city_id) || empty($departure_date)) {
        die("<p>Missing required fields. Please fill in all fields.</p>");
    }

    // Convert dates to proper format
    $departure_date = date('Y-m-d', strtotime($departure_date));
    $return_date = $return_date ? date('Y-m-d', strtotime($return_date)) : null;

    // Function to fetch airports by city ID
    function getAirportCodeByCityId($city_id, $pdo) {
        $stmt = $pdo->prepare("
            SELECT airport_code 
            FROM airports 
            WHERE city_id = :city_id
            LIMIT 1
        ");
        $stmt->execute([':city_id' => $city_id]);
        return $stmt->fetchColumn();
    }

    // Function to fetch flights based on parameters
    function getFlights($departure_airport, $arrival_airport, $flight_date, $pdo, $sort_order) {
        $query = "
            SELECT f.flight_id, f.flight_number, f.departure_time, f.arrival_time, a.name AS airline, 
                   ROUND((100 + RAND() * 200), 2) AS ticket_price
            FROM flights f
            JOIN airlines a ON f.airline_id = a.airline_id
            WHERE f.departure_airport = :departure_airport
              AND f.arrival_airport = :arrival_airport
              AND DATE(f.departure_time) = :flight_date
            ORDER BY ticket_price $sort_order
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':departure_airport' => $departure_airport,
            ':arrival_airport' => $arrival_airport,
            ':flight_date' => $flight_date,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get airport codes based on city IDs
    $departure_airport = getAirportCodeByCityId($departure_city_id, $pdo);
    $arrival_airport = getAirportCodeByCityId($arrival_city_id, $pdo);

    if (!$departure_airport || !$arrival_airport) {
        die("<p>Invalid departure or arrival city.</p>");
    }

    // Query for departure flights
    $departure_flights = getFlights($departure_airport, $arrival_airport, $departure_date, $pdo, $sort_order);

    // Query for return flights if applicable
    $return_flights = [];
    if ($travel_type === 'return' && $return_date) {
        $return_flights = getFlights($arrival_airport, $departure_airport, $return_date, $pdo, $sort_order);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <h1>Available Flights</h1>

    <!-- Sorting Dropdown -->
    <div class="sort-dropdown">
        <label for="sort-order">Sort by:</label>
        <select id="sort-order" name="sort_order" onchange="submitSorting()">
            <option value="asc" <?= isset($sort_order) && $sort_order === 'asc' ? 'selected' : '' ?>>Lowest Price</option>
            <option value="desc" <?= isset($sort_order) && $sort_order === 'desc' ? 'selected' : '' ?>>Highest Price</option>
        </select>
    </div>

    <form id="sorting-form" method="POST" action="">
        <input type="hidden" name="departure_city" value="<?= htmlspecialchars($departure_city) ?>">
        <input type="hidden" name="arrival_city" value="<?= htmlspecialchars($arrival_city) ?>">
        <input type="hidden" name="departure_date" value="<?= htmlspecialchars($departure_date) ?>">
        <input type="hidden" name="return_date" value="<?= htmlspecialchars($return_date) ?>">
        <input type="hidden" name="trip_type" value="<?= htmlspecialchars($trip_type) ?>">
        <input type="hidden" name="sort_order" id="hidden-sort-order" value="<?= htmlspecialchars($sort_order) ?>">
    </form>

    <!-- Display Departure Flights -->
    <?php if ($travel_type === 'one_way'): ?>
        <h2>One-Way Flights</h2>
        <!-- Display one-way flight details here -->
        <?php if (!empty($departure_flights)): ?>
        <?php foreach ($departure_flights as $flight): ?>
            <div class="flight-card">
                <p><strong>Flight Number:</strong> <?= htmlspecialchars($flight['flight_number']) ?></p>
                <p><strong>Airline:</strong> <?= htmlspecialchars($flight['airline']) ?></p>
                <p><strong>Departure Time:</strong> <?= htmlspecialchars($flight['departure_time']) ?></p>
                <p><strong>Arrival Time:</strong> <?= htmlspecialchars($flight['arrival_time']) ?></p>
                <p><strong>Price:</strong> $<?= htmlspecialchars($flight['ticket_price']) ?></p>
                <form method="POST" action="buy_ticket.php">
                    <input type="hidden" name="flight_id" value="<?= $flight['flight_id'] ?>">
                    <input type="hidden" name="ticket_price" value="<?= $flight['ticket_price'] ?>">
                    <button type="submit" class="buy-button">Buy Ticket</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No departure flights found for the selected criteria.</p>
    <?php endif; ?>
     <!-- Display Return Flights if applicable -->
     <?php elseif ($travel_type === 'return'): ?>
        <h2>Return Flights</h2>
        <?php if (!empty($return_flights)): ?>
            <?php foreach ($return_flights as $flight): ?>
                <?php if (!empty($departure_flights)): ?>
        <?php foreach ($departure_flights as $flight): ?>
            <div class="flight-card">
                <p><strong>Flight Number:</strong> <?= htmlspecialchars($flight['flight_number']) ?></p>
                <p><strong>Airline:</strong> <?= htmlspecialchars($flight['airline']) ?></p>
                <p><strong>Departure Time:</strong> <?= htmlspecialchars($flight['departure_time']) ?></p>
                <p><strong>Arrival Time:</strong> <?= htmlspecialchars($flight['arrival_time']) ?></p>
                <p><strong>Price:</strong> $<?= htmlspecialchars($flight['ticket_price']) ?></p>
                <form method="POST" action="buy_ticket.php">
                    <input type="hidden" name="flight_id" value="<?= $flight['flight_id'] ?>">
                    <input type="hidden" name="ticket_price" value="<?= $flight['ticket_price'] ?>">
                    <button type="submit" class="buy-button">Buy Ticket</button>
                </form>
            </div>
            <?php endforeach; ?>
    <?php else: ?>
        <p>No departure flights found for the selected criteria.</p>
    <?php endif; ?>
                <div class="flight-card">
                    <p><strong>Flight Number:</strong> <?= htmlspecialchars($flight['flight_number']) ?></p>
                    <p><strong>Airline:</strong> <?= htmlspecialchars($flight['airline']) ?></p>
                    <p><strong>Departure Time:</strong> <?= htmlspecialchars($flight['departure_time']) ?></p>
                    <p><strong>Arrival Time:</strong> <?= htmlspecialchars($flight['arrival_time']) ?></p>
                    <p><strong>Price:</strong> $<?= htmlspecialchars($flight['ticket_price']) ?></p>
                    <form method="POST" action="buy_ticket.php">
                        <input type="hidden" name="flight_id" value="<?= htmlspecialchars($flight['flight_id']) ?>">
                        <input type="hidden" name="ticket_price" value="<?= htmlspecialchars($flight['ticket_price']) ?>">
                        <button type="submit" class="buy-button">Buy Ticket</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No return flights found for the selected criteria.</p>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        function submitSorting() {
            const sortOrder = document.getElementById('sort-order').value;
            document.getElementById('hidden-sort-order').value = sortOrder;
            document.getElementById('sorting-form').submit();
        }
    </script>
</body>
</html>
