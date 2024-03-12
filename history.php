<?php
session_start(); // Rozpocznij sesję na początku pliku
if (!isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: login.php");
}
include 'connect_database.php';
echo "<a href='index.php'><img src='img/start.png' style='width: 5%'/></a>";

$query = "SELECT vis_nick FROM uzytkownicy WHERE nick = ?";
// Pobieranie nazwy użytkownika z URL
$nick = isset($_GET['nick']) ? $conn->real_escape_string($_GET['nick']) : '';
// Przygotowanie i wykonanie zapytania
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("s", $nick); // "s" oznacza, że parametr jest typu string
    $stmt->execute();
    
    $result = $stmt->get_result(); // Pobranie wyników zapytania
    if ($row = $result->fetch_assoc()) {
        $vis_nick = $row['vis_nick']; // Pobranie vis_nick z wyniku zapytania
    }
    
    $stmt->close(); // Zamknięcie zapytania
}
echo "<h1>Historia $vis_nick</h1>";

// Zapytanie do bazy danych, aby pobrać historię połowów danego użytkownika
$query = "SELECT * FROM polowy WHERE uzytkownik = '$nick' ORDER BY data DESC";

// Wykonanie zapytania
$result = $conn->query($query);

// Tablica przechowująca wszystkie połowy, kluczowana datami
$catchesByDate = [];

// Przetwarzanie wyników zapytania
while ($row = $result->fetch_assoc()) {
    $date = substr($row['data'], 0, 10); // Wyciągnięcie samej daty (bez czasu)
    $catchesByDate[$date][] = $row; // Dodanie połowu do odpowiedniej daty
}

// Wyświetlanie połowów w tabelach, grupowanych po datach
foreach ($catchesByDate as $date => $catches) {
    echo "<h2>Połowy z dnia $date</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Nazwa ryby</th><th>Waga</th><th>Rozmiar</th><th>Zdjęcie nr 1</th><th>Zdjęcie nr 2</th><th>Zdjęcie nr 3</th></tr>";
    foreach ($catches as $catch) {
        echo "<tr>";
        echo "<td>{$catch['nazwa_ryby']}</td>";
        echo "<td>{$catch['waga']}</td>";
        echo "<td>{$catch['rozmiar']}</td>";
        // Tutaj możesz użyć pokaz_zdjecie.php do wyświetlenia zdjęcia, jeśli jest dostępne
        echo "<td><img src='pokaz_zdjecie.php?id=" . $catch['id'] . "&img=zdj1' width='100'></td>
        <td>" . (!empty($catch['zdj2']) ? "<img src='pokaz_zdjecie.php?id=" . $catch['id'] . "&img=zdj2' width='100'>" : '') . "</td>
        <td>" . (!empty($catch['zdj3']) ? "<img src='pokaz_zdjecie.php?id=" . $catch['id'] . "&img=zdj3' width='100'>" : '') . "</td>
        ";
        if ($_SESSION['permission'] >= 4) {
            // Guzik do usuwania, jeśli użytkownik ma odpowiednie uprawnienia
            echo "<td><form action='usun_polow.php?user=" . htmlspecialchars($nick) . "' method='post' onsubmit='return confirm(\"Czy na pewno chcesz usunąć ten połów?\");'><input type='hidden' name='id_polowu' value='{$catch['id']}'/><input type='submit' value='Usuń'/></form></td>";
        }        
        echo "</tr>";
    }
    echo "</table><br>";
}

include 'close_database.php';
?>
