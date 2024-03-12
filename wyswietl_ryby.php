<?php
session_start();
if (!isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
    <script src='main.js'></script>
</head>
<body>
<a href="index.php"><img src="img/start.png" style="width: 5%"/></a>
<?php
include 'connect_database.php'; // Dołączanie pliku connect_database.php do otwarcia połączenia

$query = "SELECT * FROM baza_ryb"; // Zapytanie SQL
$result = $conn->query($query); // Wykonanie zapytania

if ($result->num_rows > 0) {
    // Rozpoczynanie tabeli i dodawanie nagłówków
    echo "<table border='1'><tr><th>Nazwa</th><th>Minimalna Waga</th><th>Minimalny Rozmiar</th><th>Kto Dodał</th><th>Kiedy Dodał</th></tr>";
    // Wyświetlanie danych z każdego wiersza
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["nazwa"] . "</td>
                <td>" . ($row["min_waga"] == 0 ? "" : $row["min_waga"]) . "</td>
                <td>" . ($row["min_rozmiar"] == 0 ? "" : $row["min_rozmiar"]) . "</td>
                <td>" . $row["kto_dodal"] . "</td>
                <td>" . $row["kiedy_dodal"] . "</td>
                </tr>";
    }
    echo "</table>"; // Zakończenie tabeli
} else {
    echo "0 wyników";
}

// Sprawdź, czy zmienna sesyjna 'permission' istnieje i czy jej wartość jest większa lub równa 4
if (isset($_SESSION['permission']) && $_SESSION['permission'] >= 4) {
    // Jeśli tak, wyświetl formularz
    echo '<form action="dodaj_rybe.php" method="post">
            <button type="submit" name="add_fish">Dodaj rybe do tego sezonu</button>
        </form>';
}

include 'close_database.php'; // Dołączanie pliku close_database.php do zamknięcia połączenia
?>

</body>
</html>