<?php
include 'db_connection.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database.sql');
    
    // Execute the SQL script
    $pdo->exec($sql);
    
    echo "Database setup completed successfully!";
} catch (PDOException $e) {
    die("Error setting up database: " . $e->getMessage());
}
?>
