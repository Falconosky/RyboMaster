<?php
session_start(); // Rozpocznij sesję na początku pliku
?>

<!DOCTYPE html>
<html>
<head>
    <title>RyboMaster</title>
    <?php include 'header.php'; ?>
</head>
<body>
<a href="index.php" id="img_start"><img src="img/start.png"/></a>

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
echo "<div class='welcome'>Witaj " . htmlspecialchars($userData['vis_nick'] ?? '') . "</div>";

// Pobierz wszystkie ryby z bazy danych
$allFishQuery = "SELECT nazwa, min_rozmiar, min_waga FROM baza_ryb";
$allFishResult = $conn->query($allFishQuery);

// Pobierz ryby złapane przez użytkownika
$caughtFishQuery = $conn->prepare("SELECT DISTINCT nazwa_ryby FROM polowy WHERE uzytkownik = ?");
$caughtFishQuery->bind_param("s", $user);
$caughtFishQuery->execute();
$caughtFishResult = $caughtFishQuery->get_result();

$caughtFish = [];
while ($fish = $caughtFishResult->fetch_assoc()) {
    $caughtFish[] = $fish['nazwa_ryby'];
}

// Tworzymy listę wszystkich ryb
$allFish = [];
while ($fish = $allFishResult->fetch_assoc()) {
    $allFish[$fish['nazwa']] = $fish; // Poprawiono klucz, na podstawie którego zapisywane są informacje o rybie
}

// Wyszukujemy ryby, których użytkownik jeszcze nie złapał
$notCaughtFish = array_diff_key($allFish, array_flip($caughtFish)); // Użyto array_flip dla lepszej wydajności

// Wyświetlenie tabeli z rybami, których użytkownik jeszcze nie złapał
echo "<div class='fishes_left'><h2>Ryby, których jeszcze nie złapałeś:</h2>";
if (!empty($notCaughtFish)) {
    echo "<table border='1'>
    <tr>
        <th>Nazwa ryby</th>
        <th>Minimalna waga [kg]</th>
        <th>Minimalny rozmiar [cm]</th>
    </tr>";

    foreach ($notCaughtFish as $name => $info) {
        echo "<tr>
            <td>$name</td>
            <td>" . ($info['min_waga'] ? $info['min_waga'] : '') . "</td>
            <td>" . ($info['min_rozmiar'] ? $info['min_rozmiar'] : '') . "</td>
        </tr>";
    }
    echo "</table></div>";
} else {
    echo "<div class='fishes_left'><p>Gratulacje! Złapałeś już wszystkie dostępne ryby!</p></div>";
}
?>

<form action="wyswietl_ryby.php" method="post">
    <button type="submit" name="wyswietl">Wyswietl wszystkie ryby tego sezonu</button>
</form>
<form action="dodaj_polow.php" method="post">
    <button type="submit">Dodaj połów</button>
</form>
<form action="topka.php" method="post">
    <button type="submit">Zobacz wyniki</button>
</form>
<form action="show_profile.php" method="post">
    <button type="submit">Wyswietl szczegóły konta</button>
</form>

<?php

$uzytkownik = $_SESSION['user'];

// Zapytanie SQL do pobrania polowów danego użytkownika, posortowanych według wagi
$query = "SELECT id, nazwa_ryby, waga, rozmiar, miejscowka, data, zdj1, zdj2, zdj3 FROM polowy WHERE uzytkownik = ? ORDER BY data DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $uzytkownik);
$stmt->execute();
$result = $stmt->get_result();

echo "<div class='catches-container'>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='catch-item'>
                <div class='catch-info'>
                    <div class='opis_ryby'>Nazwa ryby: " . htmlspecialchars($row['nazwa_ryby']) . "</div>
                    <div class='opis_ryby'>Waga: " . htmlspecialchars($row['waga']) . "kg" . "</div>
                    <div class='opis_ryby'>Rozmiar: " . ($row['rozmiar'] > 0 ? htmlspecialchars($row['rozmiar']) : '') . "cm" . "</div>
                    <div class='opis_ryby'>Miejscówka: " . htmlspecialchars($row['miejscowka']) . "</div>
                    <div class='opis_ryby'>Data: " . htmlspecialchars($row['data']) . "</div>
                </div>
                <div class='catch-photos'>
                    <div class='img_shadow'><div class='crop_photo'>
                        <img class='clickable-image' src='pokaz_zdjecie.php?id=" . $row['id'] . "&img=zdj1'>
                    </div></div>
                    " . (!empty($row['zdj2']) ? "
                    <div class='img_shadow'><div class='crop_photo'>
                        <img class='clickable-image' src='pokaz_zdjecie.php?id=" . $row['id'] . "&img=zdj2'>
                    </div></div>
                    " : '') . "
                    " . (!empty($row['zdj3']) ? "
                    <div class='img_shadow'><div class='crop_photo'>
                        <img class='clickable-image' src='pokaz_zdjecie.php?id=" . $row['id'] . "&img=zdj3'>
                    </div></div>
                    " : '') . "
                </div>
                <div class='catch-action'>
                    <form action='usun_polow_index.php' method='post' onsubmit='return confirm(\"Czy na pewno chcesz usunąć ten połów?\");'>
                        <input type='hidden' name='id_polowu' value='" . $row['id'] . "'/>
                        <input type='submit' value='Usuń'/>
                    </form>
                </div>
              </div>";
    }
} else {
    echo "<div>Brak polowów.</div>";
}

echo "</div>"; // Zamknięcie diva zawierającego wszystkie połowy

$stmt->close();

echo "<a href='?logout'>Wyloguj</a>";
// Sprawdź, czy zmienna sesyjna 'permission' istnieje i czy jej wartość jest większa lub równa 4
if (isset($_SESSION['permission']) && $_SESSION['permission'] >= 5) {
    // Jeśli tak, wyświetl formularz
    echo '<form action="user_manager.php" method="post">
            <button type="submit" name="user_manager">Zarzadzaj użytkownikami</button>
        </form>';
}

include 'close_database.php'; // Dołączenie pliku odpowiedzialnego za zamknięcie połączenia z bazą danych
?>


</body>
</html>