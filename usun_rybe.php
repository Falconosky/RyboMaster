<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany i ma odpowiednie uprawnienia
if (!isset($_SESSION['permission']) || $_SESSION['permission'] < 4) {
    echo "Brak dostępu.";
    exit;
}

if (!isset($_GET['nazwa'])) {
    echo "Nie podano nazwy ryby.";
    exit;
}

include 'connect_database.php';

// Pobranie nazwy ryby z parametru GET i zabezpieczenie przed SQL Injection
$nazwa = $conn->real_escape_string($_GET['nazwa']);

// Najpierw usuwamy wszystkie połowy odpowiadające nazwie usuwanej ryby
$sql_usun_polowy = "DELETE FROM polowy WHERE nazwa_ryby = ?";
$stmt = $conn->prepare($sql_usun_polowy);
$stmt->bind_param("s", $nazwa);
if (!$stmt->execute()) {
    echo "Wystąpił błąd podczas usuwania połowów.";
    $stmt->close();
    include 'close_database.php';
    exit;
}

// Następnie usuwamy rybę z tabeli baza_ryb
$sql_usun_rybe = "DELETE FROM baza_ryb WHERE nazwa = ?";
$stmt = $conn->prepare($sql_usun_rybe);
$stmt->bind_param("s", $nazwa);
if ($stmt->execute()) {
    header("Location: wyswietl_ryby.php");
} else {
    echo "Wystąpił błąd podczas usuwania ryby.";
}

$stmt->close();
include 'close_database.php';
?>
