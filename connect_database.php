<?php
$servername = "localhost";
$username = "ja";
$password = "12345";
$database = "rybomaster";

// Tworzenie połączenia
$conn = new mysqli($servername, $username, $password, $database);

// Sprawdzanie połączenia
if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

echo "Połączono pomyślnie";
$conn->close();
?>
