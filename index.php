<?php
session_start(); // Rozpocznij sesję na początku pliku
?>

<!DOCTYPE html>
<html>
<head>
    <title>RyboMaster</title>
</head>
<body>
<a href="index.php"><img src="img/start.png" style="width: 5%"/></a>
<?php

if (isset($_GET['logout'])) {
    // Usuń dane użytkownika z sesji
    unset($_SESSION['user']);
    // Zniszcz sesję
    session_destroy();
    // Przekieruj do strony logowania
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: login.php");
}

include 'connect_database.php';
$user = $_SESSION['user'] ?? ''; // Pobranie loginu użytkownika z sesji
// Pobranie danych użytkownika z bazy
$query = $conn->prepare("SELECT vis_nick, password FROM uzytkownicy WHERE nick = ?");
$query->bind_param("s", $user);
$query->execute();
$result = $query->get_result();
$userData = $result->fetch_assoc();
// Tutaj kod dla zalogowanych użytkowników
echo "Witaj " . htmlspecialchars($userData['vis_nick'] ?? '') . "<br>" . "<br>";


?>
<?php
// Sprawdź, czy zmienna sesyjna 'permission' istnieje i czy jej wartość jest większa lub równa 4
if (isset($_SESSION['permission']) && $_SESSION['permission'] >= 4) {
    // Jeśli tak, wyświetl formularz
    echo '<form action="dodaj_rybe.php" method="post">
            <button type="submit" name="add_fish">Dodaj rybe do tego sezonu</button>
        </form>';
}
?>

<?php
// Sprawdź, czy zmienna sesyjna 'permission' istnieje i czy jej wartość jest większa lub równa 4
if (isset($_SESSION['permission']) && $_SESSION['permission'] >= 5) {
    // Jeśli tak, wyświetl formularz
    echo '<form action="user_manager.php" method="post">
            <button type="submit" name="user_manager">Zarzadzaj użytkownikami</button>
        </form>';
}
?>

<form action="wyswietl_ryby.php" method="post">
    <button type="submit" name="wyswietl">Wyswietl wszystkie ryby tego sezonu</button>
</form>
<form action="dodaj_polow.php" method="post">
    <button type="submit">Dodaj połów</button>
</form>
<form action="show_profile.php" method="post">
    <button type="submit">Wyswietl szczegóły konta</button>
</form>

<a href="?logout">Wyloguj</a>

<?php

$uzytkownik = $_SESSION['user'];

// Zapytanie SQL do pobrania polowów danego użytkownika, posortowanych według wagi
$query = "SELECT nazwa_ryby, waga, rozmiar, miejscowka, data FROM polowy WHERE uzytkownik = ? ORDER BY waga DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $uzytkownik);
$stmt->execute();
$result = $stmt->get_result();

// Rozpoczęcie tworzenia tabeli HTML do wyświetlenia polowów
echo "<table border='1'>
<tr>
    <th>Nazwa ryby</th>
    <th>Waga</th>
    <th>Rozmiar</th>
    <th>Miejscówka</th>
    <th>Data</th>
</tr>";

// Wypełnienie tabeli danymi
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row['nazwa_ryby']) . "</td>
            <td>" . htmlspecialchars($row['waga']) . "</td>
            <td>" . ($row['rozmiar'] > 0 ? htmlspecialchars($row['rozmiar']) : '') . "</td>
            <td>" . htmlspecialchars($row['miejscowka']) . "</td>
            <td>" . htmlspecialchars($row['data']) . "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5'>Brak polowów.</td></tr>";
}

echo "</table>"; // Zamknięcie tabeli

$stmt->close();
include 'close_database.php'; // Dołączenie pliku odpowiedzialnego za zamknięcie połączenia z bazą danych
?>


</body>
</html>