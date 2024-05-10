<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'connect_database.php';

// Pobranie listy użytkowników wraz z liczbą różnych złapanych przez nich ryb, posortowanych od największej do najmniejszej liczby
$usersQuery = "
SELECT uzytkownik, COUNT(DISTINCT nazwa_ryby) AS liczba_zlapanych_ryb
FROM polowy
GROUP BY uzytkownik
ORDER BY liczba_zlapanych_ryb DESC";

$usersResult = $conn->query($usersQuery);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Topka użytkowników</title>
    <?php include 'header.php'; ?>
</head>
<body>
<a href='index.php' id='img_start'><img src='img/start.png'/></a>
<h1>Topka użytkowników</h1>";

while ($userRow = $usersResult->fetch_assoc()) {
    $user = $userRow['uzytkownik'];
    $numberOfCaughtFish = $userRow['liczba_zlapanych_ryb'];

    // Pobranie widocznej nazwy użytkownika
    $visNickQuery = "SELECT vis_nick FROM uzytkownicy WHERE nick = ?";
    if ($stmt = $conn->prepare($visNickQuery)) {
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $vis_nick = $row['vis_nick'];
        }
        $stmt->close();
    }

    echo "<h2>Użytkownik <a href='history.php?nick=$user'>$vis_nick</a> złapał $numberOfCaughtFish różnych ryb</h2>";

    // Pobierz informacje o złapanych rybach przez użytkownika
    $caughtFishQuery = "
    SELECT nazwa_ryby, MAX(waga) AS max_waga, MAX(rozmiar) AS max_rozmiar
    FROM polowy
    WHERE uzytkownik = ?
    GROUP BY nazwa_ryby
    ORDER BY max_waga DESC, max_rozmiar DESC";

    $caughtFishStmt = $conn->prepare($caughtFishQuery);
    $caughtFishStmt->bind_param("s", $user);
    $caughtFishStmt->execute();
    $caughtFishResult = $caughtFishStmt->get_result();

    if ($caughtFishResult->num_rows > 0) {
        echo "<table border='1'>
        <tr>
            <th>Nazwa ryby</th>
            <th>Największa waga</th>
            <th>Największy rozmiar</th>
        </tr>";

        while ($fish = $caughtFishResult->fetch_assoc()) {
            echo "<tr>
            <td>{$fish['nazwa_ryby']}</td>
            <td>{$fish['max_waga']} kg</td>
            <td>{$fish['max_rozmiar']} cm</td>
            </tr>";
        }
        echo "</table><br>";
    } else {
        echo "<p>Użytkownik $user nie złapał jeszcze żadnych ryb.</p>";
    }
}

echo "</body></html>";
$conn->close();
?>
