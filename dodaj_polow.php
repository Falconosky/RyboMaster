<?php
session_start();

// Sprawdzenie, czy formularz został wysłany
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj'])) {
    include 'connect_database.php'; // Zakładam, że ten plik istnieje i nawiązuje połączenie z bazą danych

    $nazwaRyby = $_POST['nazwaRyby'];
    $waga = $_POST['waga'] ?? 0;
    $rozmiar = $_POST['rozmiar'] ?? 0;
    $miejscowka = $_POST['miejscowka'];
    $data = $_POST['data'];
    $uzytkownik = $_SESSION['user'];

    // Walidacja czy ryba znajduje się w bazie "baza_ryb"
    $sqlCheck = "SELECT * FROM baza_ryb WHERE nazwa = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $nazwaRyby);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    if ($resultCheck->num_rows == 0) {
        echo "Ryba nie bierze udziału w zawodach.";
    } elseif (empty($waga) || (!empty($rozmiar) || !empty($miejscowka))) {
        // Tutaj logika obsługująca przesyłanie zdjęć i zapisywanie danych do bazy
        // Przykład zakłada, że zdjęcia są przechowywane w folderze 'uploads' i zapisywane są nazwy plików

        $zdjecie1 = $_FILES['zdjecie1']['name'];
        move_uploaded_file($_FILES['zdjecie1']['tmp_name'], "uploads/" . $zdjecie1);

        $zdjecie2 = $_FILES['zdjecie2']['name'] ? $_FILES['zdjecie2']['name'] : null;
        if ($zdjecie2) {
            move_uploaded_file($_FILES['zdjecie2']['tmp_name'], "uploads/" . $zdjecie2);
        }

        $zdjecie3 = $_FILES['zdjecie3']['name'] ? $_FILES['zdjecie3']['name'] : null;
        if ($zdjecie3) {
            move_uploaded_file($_FILES['zdjecie3']['tmp_name'], "uploads/" . $zdjecie3);
        }

        $sql = "INSERT INTO polowy (uzytkownik, nazwa_ryby, waga, rozmiar, miejscowka, data, zdj1, zdj2, zdj3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddsssss", $uzytkownik, $nazwaRyby, $waga, $rozmiar, $miejscowka, $data, $zdjecie1, $zdjecie2, $zdjecie3);
        $stmt->execute();

        echo "Polów został dodany.";
    } else {
        echo "Wymagane dane są niekompletne.";
    }

    include 'close_database.php';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>RyboMaster</title>
</head>
<body>
<a href="index.php"><img src="img/start.png" style="width: 5%"/></a>

<form method="post" action="" enctype="multipart/form-data">
    <input type="text" name="nazwaRyby" placeholder="Nazwa ryby" required><br>
    <input type="number" name="waga" placeholder="Waga ryby" required><br>
    <input type="number" name="rozmiar" placeholder="Rozmiar ryby"><br>
    <input type="text" name="miejscowka" placeholder="Miejscówka"><br>
    <input type="date" name="data" required><br>
    Zdjęcie (wymagane): <input type="file" name="zdjecie1" required><br>
    Opcjonalne zdjęcie 2: <input type="file" name="zdjecie2"><br>
    Opcjonalne zdjęcie 3: <input type="file" name="zdjecie3"><br>
    <button type="submit" name="dodaj">Dodaj polów</button>
</form>

</body>
</html>
