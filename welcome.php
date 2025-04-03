
<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Booking</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-nav">
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
    <div class="top-right-buttons">
        <button id="historyButton" onclick="viewHistory()">View Booking History</button>
        <button id="contactButton" onclick="openContactDialog()">Contact Admin</button>
    </div>
    <div class="booking-container">

        <h2>Book Your Ticket</h2>
        <form method="POST" action="search-flights.php">
            <!-- Travel Type Selection -->
            <div class="form-group">
            <input type="radio" id="one_way" name="travel_type" value="one_way" onclick="toggleReturnCalendar()" checked>
            <label for="one_way">One Way</label><br>
            <input type="radio" id="return" name="travel_type" value="return" onclick="toggleReturnCalendar()">
            <label for="return">Return</label><br>
        </div>
            <!-- From City -->
            <div class="form-group">
                <label for="from-city">From:</label>
                <select name="departure_airport" id="from-city" required>
                    <option value="">Select City</option>
        <?php
        include 'db_connection.php'; // Include your database connection
        
        try {
            // Fetch cities from the database
            $stmt = $pdo->query("SELECT city_id, name FROM cities ORDER BY name ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . htmlspecialchars($row['city_id']) . '">' . htmlspecialchars($row['name']) . '</option>';
            }
        } catch (PDOException $e) {
            // Handle errors
            error_log("Error fetching cities: " . $e->getMessage());
            echo '<option value="">Error loading cities</option>';
        }
        ?>
    </select>
            </div>

            <!-- To City -->
            <div class="form-group">
                <label for="to-city">To:</label>
                <select name="arrival_airport" id="to-city" required>
                <option value="">Select City</option>
        <?php
        include 'db_connection.php'; // Include your database connection
        
        try {
            // Fetch cities from the database
            $stmt = $pdo->query("SELECT city_id, name FROM cities ORDER BY name ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . htmlspecialchars($row['city_id']) . '">' . htmlspecialchars($row['name']) . '</option>';
            }
        } catch (PDOException $e) {
            // Handle errors
            error_log("Error fetching cities: " . $e->getMessage());
            echo '<option value="">Error loading cities</option>';
        }
        ?>
                </select>
            </div>

            <!-- Departure Date -->
            <div class="form-group">
                <label for="departure-date">Departure Date:</label>
                <input type="date" name="departure_date" id="departure-date" required>
            </div>

            <!-- Return Date -->
            <div class="form-group hidden" id="return-date-group" style="display: none;">
                <label for="return-date">Return Date:</label>
                <input type="date" name="return_date" id="return-date">
            </div>

            <!-- Search Button -->
            <div class="form-group">
                <button type="submit" name="search-flights">Search Flights</button>
            </div>
        </form>
    </div>
        <!-- History Modal -->
        <dialog id="historyModal">
            <h2>Your Previous Trips</h2>
            <ul id="tripList">

            </ul>
            <div class="modal-buttons">
                <button type="button" id="closeHistoryModalButton">Close</button>
            </div>
        </dialog>
      <!-- Contact Modal -->
      <dialog id="contactModal">
        <h2>Contact</h2>
        <form id="contactForm" method="POST" action="process_contact.php" onsubmit="return handleContactSubmit(event)">
        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
        </div>
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea id="message" name="message" required></textarea>
        </div>
        <div class="form-group">
            <button type="submit">Send Message</button>
            <button type="button" onclick="closeContactModal()">Cancel</button>
        </div>
    </form>
    </dialog>
</div>

<dialog id="historyModal">
    <h2>Booking History</h2>
    <ul id="tripList"></ul>
    <button onclick="document.getElementById('historyModal').close();">Close</button>
</dialog>


<script>
function toggleReturnCalendar() {
            const returnDateGroup = document.getElementById('return-date-group');
            const returnDateInput = document.getElementById('return-date');
            const isReturnSelected = document.getElementById('return').checked;

            if (isReturnSelected) {
                returnDateGroup.style.display = "block";
                returnDateInput.required = true; 
            } else {
                returnDateGroup.style.display = "none";
                returnDateInput.required = false; 
                returnDateInput.value = ""; 
            }
        }


function viewHistory() {
    const tripList = document.getElementById('tripList');
    tripList.innerHTML = '<p>Loading...</p>'; // Show a loading state

    // Fetch booking history dynamically
    fetch('fetch_history.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            tripList.innerHTML = ''; // Clear previous data
            if (data.status === 'success' && data.data.length > 0) {
                data.data.forEach(trip => {
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `
                        <strong>Flight Number:</strong> ${trip.flight_number}<br>
                        <strong>Trasa:</strong> ${trip.departure_airport} -> ${trip.arrival_airport}<br>
                        <strong>Booking Date:</strong> ${trip.booking_date}<br>
                        <strong>Status Rezerwacji:</strong> ${trip.status}<br>
                    `;
                    tripList.appendChild(listItem);
                });
            } else if (data.status === 'error') {
                tripList.innerHTML = `<p>${data.message}</p>`;
            } else {
                tripList.innerHTML = '<p>No booking history found.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching history:', error);
            tripList.innerHTML = '<p>Error loading history.</p>';
        });

    // Show the modal
    document.getElementById('historyModal').showModal();
}

function closeHistoryModal() {
    document.getElementById('historyModal').close();
}

    function logout() {
            window.location.href = 'logout.php';
        }
 function openContactDialog() {
        document.getElementById('contactModal').showModal();
    }

    // Close the contact modal
    function closeContactModal() {
        document.getElementById('contactModal').close();
    }

    // Handle form submission dynamically
    function handleContactSubmit(event) {
        event.preventDefault();

        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value;

        fetch('process_contact.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                subject,
                message
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Message sent successfully!');
                    closeContactModal();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the message.');
            });
    }
    document.addEventListener('DOMContentLoaded', () => {
        // Toggle Return Date Field Based on Travel Type

// Ensure the default state is set on page load
window.onload = function () {
    toggleReturnCalendar();
};


        // Set default state on page load
        toggleReturnCalendar();

        const historyButton = document.getElementById('historyButton');
        const historyModal = document.getElementById('historyModal');
        const closeHistoryModalButton = document.getElementById('closeHistoryModalButton');
        const contactButton = document.getElementById('contactButton');
        const contactModal = document.getElementById('contactModal');
        const closeContactModalButton = document.getElementById('closeContactModalButton');
        const tripList = document.getElementById('tripList');

        // Open the history modal when the History button is clicked
        historyButton.addEventListener('click', () => {
            tripList.innerHTML = '';
            userTrips.forEach(trip => {
                const listItem = document.createElement('li');
                listItem.textContent = `Date: ${trip.date}, Destination: ${trip.destination}, Status: ${trip.status}`;
                tripList.appendChild(listItem);
            });

            historyModal.showModal();
        });

        // Close the history modal when the Close button is clicked
        closeHistoryModalButton.addEventListener('click', () => {
            historyModal.close();
        });

        // Open the contact modal when the Contact button is clicked
        contactButton.addEventListener('click', () => {
            contactModal.showModal();
        });

        // Close the contact modal when the Cancel button is clicked
        closeContactModalButton.addEventListener('click', () => {
            contactModal.close();
        });

        const closeModalButton = document.getElementById('closeModalButton');

        // Close the modal when the Cancel button is clicked
        closeModalButton.addEventListener('click', () => {
            contactModal.close();
        });
    });
</script>
</body>
</html>
