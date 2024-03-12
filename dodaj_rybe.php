<?php
session_start();
if (!isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: login.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['nazwa']) && (!empty($_POST['min_waga']) || !empty($_POST['min_rozmiar']))) {
    include 'connect_database.php';
    
    // Przekształcenie wartości 'nazwa' na format "Pierwsza duża, reszta małe litery"
    $nazwa = ucfirst(strtolower($_POST['nazwa']));
    $minWaga = !empty($_POST['min_waga']) ? $_POST['min_waga'] : 0;
    $minRozmiar = !empty($_POST['min_rozmiar']) ? $_POST['min_rozmiar'] : 0;
    $ktoDodal = $_SESSION['user'];
    $kiedyDodal = date('Y-m-d H:i:s');

    // Sprawdzanie, czy wpis o tej samej nazwie już istnieje
    $sql_check = "SELECT COUNT(*) FROM baza_ryb WHERE nazwa = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $nazwa);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        echo "Wpis o tej samej nazwie ryby już istnieje.";
    } else {
        // Wpis nie istnieje, więc dodajemy nowy rekord
        $sql = "INSERT INTO baza_ryb (nazwa, min_waga, min_rozmiar, kto_dodal, kiedy_dodal) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sddss", $nazwa, $minWaga, $minRozmiar, $ktoDodal, $kiedyDodal);
        $stmt->execute();
        $stmt->close();
        
        header("Location: wyswietl_ryby.php");
    }

    include 'close_database.php';
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Wszystkie pola są wymagane.";
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>RyboMaster - nowa ryba</title>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
</head>
<body>
<a href="index.php"><img src="img/start.png" style="width: 5%"/></a>
<form method="post" action="">
    <input type="text" name="nazwa" placeholder="Nazwa ryby" required>
    <input type="number" step="0.01" name="min_waga" placeholder="Minimalna waga">
    <input type="number" step="0.01" name="min_rozmiar" placeholder="Minimalny rozmiar">
    <button type="submit">Dodaj rybę</button>
</form>

</body>
</html>
