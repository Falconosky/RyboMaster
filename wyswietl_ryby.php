<?php

if (isset($_POST['wyswietl'])) {
    include 'connect_database.php'; // Dołączanie pliku connect.php do otwarcia połączenia

$query = "SELECT * FROM baza_ryb"; // Zapytanie SQL
$result = $conn->query($query); // Wykonanie zapytania

if ($result->num_rows > 0) {
    // Wyświetlanie danych z każdego wiersza
    while($row = $result->fetch_assoc()) {
        echo "Nazwa: " . $row["nazwa"] . "<br>";
    }
} else {
    echo "0 wyników";
}

include 'close_database.php'; // Dołączanie pliku close.php do zamknięcia połączenia
}
?>