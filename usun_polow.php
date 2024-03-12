<?php
session_start();

$nick = $_GET['user'];
include 'connect_database.php';

// Sprawdzenie, czy użytkownik ma wystarczające uprawnienia do usunięcia połowu
if ($_SESSION['permission'] < 4) {
    die("Brak wystarczających uprawnień.");
}

// Walidacja danych przekazanych metodą POST
if (!isset($_POST['id_polowu']) || !is_numeric($_POST['id_polowu'])) {
    die("Nieprawidłowe dane.");
}

$id_polowu = $_POST['id_polowu'];

// Przygotowanie zapytania do usunięcia połowu
$query = "DELETE FROM polowy WHERE id = ?";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $id_polowu); // "i" oznacza, że parametr jest typu integer
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "";
    }

    $stmt->close();
} else {
    echo "Błąd przy przygotowywaniu zapytania: " . $conn->error;
}

$conn->close();

echo "$nick";
// Przekierowanie z powrotem do historii użytkownika
header('Location: history.php?nick=' . urlencode($nick));
exit();
?>
