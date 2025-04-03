<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_id = $_POST['flight_id'] ?? null;
    $ticket_price = $_POST['ticket_price'] ?? null;

    if (!$flight_id || !$ticket_price) {
        die("<p>Invalid flight selection. Please try again.</p>");
    }

    // Simulate luggage inclusion for demonstration (replace with dynamic data if applicable)
    $luggage_included = false; // Assume luggage is not included by default
    $additional_luggage_cost = 50; // Cost of additional luggage
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Luggage and Payment Method</title>
    <style>
        .container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }
        .container h1 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group select, .form-group input, .form-group button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
        }
        .form-group button {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Additional Options</h1>
        <form method="POST" action="process_payment.php">
            <input type="hidden" name="flight_id" value="<?= htmlspecialchars($flight_id) ?>">
            <input type="hidden" name="ticket_price" value="<?= htmlspecialchars($ticket_price) ?>">

            <!-- Luggage Option -->
            <div class="form-group">
                <label>Luggage:</label>
                <p>No luggage is included. Add luggage with the following options:</p>
                <label for="num-suitcases">Number of Suitcases:</label>
                <select id="num-suitcases" name="num_suitcases">
                    <option value="0">0 - No additional cost</option>
                    <option value="1">1 suitcase - $20</option>
                    <option value="2">2 suitcases - $30</option>
                </select>
                <label for="weight-per-suitcase">Weight per Suitcase (kg):</label>
                <input type="number" id="weight-per-suitcase" name="weight_per_suitcase" min="1" max="30" placeholder="Enter weight (e.g., 16 kg)">
            </div>

            <!-- Payment Method -->
            <div class="form-group">
                <label for="payment-method">Payment Method:</label>
                <select id="payment-method" name="payment_method" required>
                    <option value="">Select Payment Method</option>
                    <option value="account">Account Balance</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="blik">BLIK</option>
                </select>
            </div>

            <!-- Card Information Section -->
            <div id="card-section" class="form-group" style="display: none;">
                <label for="new-card">New Card Number:</label>
                <input type="text" id="new-card" name="new_card" placeholder="Enter new card number">
                <label for="expiration">Expiration Date:</label>
                <input type="text" id="expiration" name="expiration" placeholder="MM/YY">
                <label for="cvv">CVV:</label>
                <input type="text" id="cvv" name="cvv" placeholder="Enter CVV">
            </div>

            <div class="form-group">
                <button type="submit">Proceed to Payment</button>
            </div>
        </form>
    </div>

    <script>
        const paymentMethodSelect = document.getElementById('payment-method');
        const cardSection = document.getElementById('card-section');

        paymentMethodSelect.addEventListener('change', () => {
            if (paymentMethodSelect.value === 'card') {
                cardSection.style.display = 'block';
            } else {
                cardSection.style.display = 'none';
            }
        });
    </script>
</body>
</html>
