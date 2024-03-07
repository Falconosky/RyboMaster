<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "rybomaster";

// Tworzenie połączenia
$conn = new mysqli($servername, $username, $password, $database);

// Sprawdzanie połączenia
if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}
?>
