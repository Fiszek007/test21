<?php
include 'db_connection.php';
session_start();

// Ensure the user is logged in and has the "Admin" role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.html");
    exit();
}

// Ensure the 'deleted' column exists in the 'users' and 'flights' tables
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN deleted TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE flights ADD COLUMN deleted TINYINT(1) DEFAULT 0");
} catch (PDOException $e) {
    if ($e->getCode() != '42S21') { // Ignore "Column already exists" error
        die("Error adding 'deleted' column: " . htmlspecialchars($e->getMessage()));
    }
}

// Fetch all users and messages
try {
    // Fetch all users
    $stmt = $pdo->prepare("SELECT user_id, login, first_name, last_name, email, phone_number, role FROM users WHERE deleted = 0");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all messages
    $stmt = $pdo->prepare("SELECT m.message_id, m.subject, m.message, u.first_name, u.last_name, u.email 
                           FROM messages m 
                           JOIN users u ON m.user_id = u.user_id
                           WHERE u.deleted = 0");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all flights
    $stmt = $pdo->prepare("SELECT flight_id, flight_number, departure_airport, arrival_airport, departure_time, arrival_time, airline_id, aircraft_type FROM flights WHERE deleted = 0");
    $stmt->execute();
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all airports
    $stmt = $pdo->prepare("SELECT airport_code, name FROM airports");
    $stmt->execute();
    $airports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching admin data: " . htmlspecialchars($e->getMessage()));
}

// Fetch all airlines
try {
    $stmt = $pdo->prepare("SELECT airline_id, name FROM airlines");
    $stmt->execute();
    $airlines = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching airlines data: " . htmlspecialchars($e->getMessage()));
}

// Fetch all archived users and flights
try {
    // Fetch archived users
    $stmt = $pdo->prepare("SELECT user_id, login, first_name, last_name, email, phone_number, role FROM users_archive");
    $stmt->execute();
    $archivedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch archived flights
    $stmt = $pdo->prepare("SELECT flight_id, flight_number, departure_airport, arrival_airport, departure_time, arrival_time, airline_id, aircraft_type FROM flights WHERE deleted = 1");
    $stmt->execute();
    $archivedFlights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching archived data: " . htmlspecialchars($e->getMessage()));
}

function deleteUser($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET deleted = 1 WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return ['status' => 'success'];
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => htmlspecialchars($e->getMessage())];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $response = deleteUser($_POST['delete_user_id']);
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Admin Panel</h1>
    <div class="admin-functions">
        <button onclick="showSection('users')">User Management</button>
        <button onclick="showSection('messages')">View Messages</button>
        <button onclick="showSection('flights')">Flight Management</button>
        <button onclick="showSection('archive')">Archive</button>
        <button onclick="logout()">Logout</button>
    </div>

    <!-- Users Management Section -->
    <div id="users-section" class="admin-section">
        <h2>User Management</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['login']) ?></td>
                        <td><?= htmlspecialchars($user['first_name']) ?></td>
                        <td><?= htmlspecialchars($user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone_number']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <button onclick="editUser(<?= $user['user_id'] ?>)">Edit</button>
                            <button onclick="deleteUser(<?= $user['user_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Messages Section -->
    <div id="messages-section" class="admin-section" style="display: none;">
        <h2>View Messages</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?= htmlspecialchars($message['message_id']) ?></td>
                        <td><?= htmlspecialchars($message['subject']) ?></td>
                        <td><?= htmlspecialchars($message['message']) ?></td>
                        <td><?= htmlspecialchars($message['first_name']) ?></td>
                        <td><?= htmlspecialchars($message['last_name']) ?></td>
                        <td><?= htmlspecialchars($message['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Flights Management Section -->
    <div id="flights-section" class="admin-section" style="display: none;">
        <h2>Flight Management</h2>
        <button onclick="openAddFlightModal()">Add Flight</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Flight Number</th>
                    <th>Departure Airport</th>
                    <th>Arrival Airport</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Airline</th>
                    <th>Aircraft Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flights as $flight): ?>
                    <tr>
                        <td><?= htmlspecialchars($flight['flight_id']) ?></td>
                        <td><?= htmlspecialchars($flight['flight_number']) ?></td>
                        <td><?= htmlspecialchars($flight['departure_airport']) ?></td>
                        <td><?= htmlspecialchars($flight['arrival_airport']) ?></td>
                        <td><?= htmlspecialchars($flight['departure_time']) ?></td>
                        <td><?= htmlspecialchars($flight['arrival_time']) ?></td>
                        <td><?= htmlspecialchars($flight['airline_id']) ?></td>
                        <td><?= htmlspecialchars($flight['aircraft_type']) ?></td>
                        <td>
                            <button onclick="editFlight(<?= $flight['flight_id'] ?>)">Edit</button>
                            <button onclick="deleteFlight(<?= $flight['flight_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Archive Section -->
    <div id="archive-section" class="admin-section" style="display: none;">
        <h2>Archive</h2>
        <h3>Archived Users</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($archivedUsers as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['login']) ?></td>
                        <td><?= htmlspecialchars($user['first_name']) ?></td>
                        <td><?= htmlspecialchars($user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone_number']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Archived Flights</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Flight Number</th>
                    <th>Departure Airport</th>
                    <th>Arrival Airport</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Airline</th>
                    <th>Aircraft Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($archivedFlights as $flight): ?>
                    <tr>
                        <td><?= htmlspecialchars($flight['flight_id']) ?></td>
                        <td><?= htmlspecialchars($flight['flight_number']) ?></td>
                        <td><?= htmlspecialchars($flight['departure_airport']) ?></td>
                        <td><?= htmlspecialchars($flight['arrival_airport']) ?></td>
                        <td><?= htmlspecialchars($flight['departure_time']) ?></td>
                        <td><?= htmlspecialchars($flight['arrival_time']) ?></td>
                        <td><?= htmlspecialchars($flight['airline_id']) ?></td>
                        <td><?= htmlspecialchars($flight['aircraft_type']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Flight Modal -->
    <dialog id="addFlightModal">
        <div class="modal-header">
            <h2>Add Flight</h2>
            <button id="closeAddFlightModal" class="close-button">&times;</button>
        </div>
        <form id="addFlightForm" method="POST" onsubmit="createFlight(event)">
            <label for="add_flight_number">Flight Number:</label>
            <input type="text" id="add_flight_number" name="flight_number" required>
            <label for="add_departure_airport">Departure Airport:</label>
            <select id="add_departure_airport" name="departure_airport" required>
                <?php foreach ($airports as $airport): ?>
                    <option value="<?= htmlspecialchars($airport['airport_code']) ?>"><?= htmlspecialchars($airport['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="add_arrival_airport">Arrival Airport:</label>
            <select id="add_arrival_airport" name="arrival_airport" required>
                <?php foreach ($airports as $airport): ?>
                    <option value="<?= htmlspecialchars($airport['airport_code']) ?>"><?= htmlspecialchars($airport['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="add_departure_time">Departure Time:</label>
            <input type="datetime-local" id="add_departure_time" name="departure_time" required>
            <label for="add_arrival_time">Arrival Time:</label>
            <input type="datetime-local" id="add_arrival_time" name="arrival_time" required>
            <label for="add_airline_id">Airline:</label>
            <select id="add_airline_id" name="airline_id" required>
                <?php foreach ($airlines as $airline): ?>
                    <option value="<?= htmlspecialchars($airline['airline_id']) ?>"><?= htmlspecialchars($airline['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="add_aircraft_type">Aircraft Type:</label>
            <input type="text" id="add_aircraft_type" name="aircraft_type">
            <button type="submit">Add</button>
        </form>
    </dialog>

    <!-- Edit Flight Modal -->
    <dialog id="editFlightModal">
        <div class="modal-header">
            <h2>Edit Flight</h2>
            <button id="closeEditFlightModal" class="close-button">&times;</button>
        </div>
        <form id="editFlightForm" method="POST" onsubmit="updateFlight(event)">
            <input type="hidden" id="edit_flight_id" name="flight_id">
            <label for="edit_departure_airport">Departure Airport:</label>
            <input type="text" id="edit_departure_airport" name="departure_airport" required>
            <label for="edit_arrival_airport">Arrival Airport:</label>
            <input type="text" id="edit_arrival_airport" name="arrival_airport" required>
            <label for="edit_departure_time">Departure Time:</label>
            <input type="datetime-local" id="edit_departure_time" name="departure_time" required>
            <label for="edit_arrival_time">Arrival Time:</label>
            <input type="datetime-local" id="edit_arrival_time" name="arrival_time" required>
            <label for="edit_airline_id">Airline:</label>
            <input type="number" id="edit_airline_id" name="airline_id" required>
            <label for="edit_aircraft_type">Aircraft Type:</label>
            <input type="text" id="edit_aircraft_type" name="aircraft_type">
            <button type="submit">Save</button>
        </form>
    </dialog>

    <script>
        function showSection(section) {
            document.getElementById('users-section').style.display = section === 'users' ? 'block' : 'none';
            document.getElementById('messages-section').style.display = section === 'messages' ? 'block' : 'none';
            document.getElementById('flights-section').style.display = section === 'flights' ? 'block' : 'none';
            document.getElementById('archive-section').style.display = section === 'archive' ? 'block' : 'none';
        }

        function editUser(userId) {
            alert('Edit functionality for user ID ' + userId + ' is not yet implemented.');
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action will move the user to the archive.')) {
                fetch('archive_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ delete_user_id: userId }) // Add user ID to the request body
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('User has been archived.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }


        const addFlightModal = document.getElementById('addFlightModal');
        const closeAddFlightModal = document.getElementById('closeAddFlightModal');

        function openAddFlightModal() {
            addFlightModal.showModal();
        }

        closeAddFlightModal.addEventListener('click', () => {
            addFlightModal.close();
        });

        function createFlight(event) {
            event.preventDefault();
            const form = document.getElementById('addFlightForm');
            const formData = new FormData(form);

            fetch('add_flight.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Flight has been added.');
                        addFlightModal.close();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        }

        const editFlightModal = document.getElementById('editFlightModal');
        const closeEditFlightModal = document.getElementById('closeEditFlightModal');

        function editFlight(flightId) {
            fetch(`get_flight.php?id=${flightId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('edit_flight_id').value = data.flight.flight_id;
                        document.getElementById('edit_departure_airport').value = data.flight.departure_airport;
                        document.getElementById('edit_arrival_airport').value = data.flight.arrival_airport;
                        document.getElementById('edit_departure_time').value = data.flight.departure_time.replace(' ', 'T');
                        document.getElementById('edit_arrival_time').value = data.flight.arrival_time.replace(' ', 'T');
                        document.getElementById('edit_airline_id').value = data.flight.airline_id;
                        document.getElementById('edit_aircraft_type').value = data.flight.aircraft_type;
                        editFlightModal.showModal();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        }

        closeEditFlightModal.addEventListener('click', () => {
            editFlightModal.close();
        });

        function updateFlight(event) {
            event.preventDefault();
            const form = document.getElementById('editFlightForm');
            const formData = new FormData(form);

            fetch('update_flight.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Flight has been updated.');
                        editFlightModal.close();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        }

        function deleteFlight(flightId) {
            if (confirm('Are you sure you want to delete this flight? This action will move the flight to the archive.')) {
                fetch(`archive_flight.php?id=${flightId}`, { method: 'GET' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Flight has been archived.');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
            }
        }


        function logout() {
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>
