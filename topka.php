<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'connect_database.php';

// Pobranie listy użytkowników, którzy złapali przynajmniej jedną rybę
$usersQuery = "SELECT DISTINCT uzytkownik FROM polowy";
$usersResult = $conn->query($usersQuery);

$allFishQuery = "SELECT nazwa FROM baza_ryb";
$allFishResult = $conn->query($allFishQuery);
$allFish = $allFishResult->fetch_all(MYSQLI_ASSOC);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Topka użytkowników</title>
</head>
<body>
<a href='index.php'><img src='img/start.png' style='width: 5%'/></a>
<h1>Topka użytkowników</h1>";

// Iteracja przez listę użytkowników i wyświetlenie ich topki
while ($userRow = $usersResult->fetch_assoc()) {
    $user = $userRow['uzytkownik'];

    $query = "SELECT vis_nick FROM uzytkownicy WHERE nick = ?";

    // Przygotowanie i wykonanie zapytania
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $user); // "s" oznacza, że parametr jest typu string
        $stmt->execute();
        
        $result = $stmt->get_result(); // Pobranie wyników zapytania
        if ($row = $result->fetch_assoc()) {
            $vis_nick = $row['vis_nick']; // Pobranie vis_nick z wyniku zapytania
        }
        
        $stmt->close(); // Zamknięcie zapytania
    }

    // Pobierz ryby złapane przez użytkownika
    $caughtFishQuery = $conn->prepare("SELECT DISTINCT nazwa_ryby FROM polowy WHERE uzytkownik = ?");
    $caughtFishQuery->bind_param("s", $user);
    $caughtFishQuery->execute();
    $caughtFishResult = $caughtFishQuery->get_result();
    $caughtFish = $caughtFishResult->fetch_all(MYSQLI_ASSOC);

    $caughtFishNames = array_column($caughtFish, 'nazwa_ryby');

    $notCaughtFish = []; // Tablica na ryby, których użytkownik jeszcze nie złapał

    foreach ($allFish as $fish) {
        $isCaught = false; // Zakładamy, że ryba nie została złapana
        
        foreach ($caughtFish as $caught) {
            if ($fish['nazwa'] == $caught['nazwa_ryby']) { // Porównujemy nazwy ryb
                $isCaught = true; // Jeśli znajdziemy dopasowanie, oznaczamy rybę jako złapaną
                break; // Nie ma potrzeby dalszego przeszukiwania
            }
        }
        
        if (!$isCaught) { // Jeśli ryba nie została oznaczona jako złapana
            $notCaughtFish[] = $fish; // Dodajemy ją do listy ryb niezłapanych
        }
    }

    echo "<h2>Ryby, których jeszcze nie złapał użytkownik <a href='history.php?nick=$user'>$vis_nick</a>:</h2>";
    if (!empty($notCaughtFish)) {
        echo "<table border='1'>
        <tr>
            <th>Nazwa ryby</th>
        </tr>";
        foreach ($notCaughtFish as $fish) {
            echo "<tr><td>{$fish['nazwa']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Użytkownik $user złapał już wszystkie ryby!</p>";
    }

    echo "<br>";
}

echo "
</body>
</html>";

$conn->close();
?>
