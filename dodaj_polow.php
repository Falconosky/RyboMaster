<?php
session_start();
if (!isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: login.php");
}
include 'connect_database.php'; // Zakładam, że ten plik istnieje i nawiązuje połączenie z bazą danych

$ryby = []; // Tablica przechowująca nazwy ryb
$sql = "SELECT nazwa FROM baza_ryb ORDER BY nazwa ASC"; // Zapytanie SQL pobierające nazwy ryb
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ryby[] = $row['nazwa']; // Dodawanie każdej nazwy ryby do tablicy
    }
}

// Sprawdzenie, czy formularz został wysłany
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj'])) {
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

        // Przygotowanie danych obrazu do zapisania w bazie
        $zdjecie1 = file_get_contents($_FILES['zdjecie1']['tmp_name']);
        $zdjecie2 = !empty($_FILES['zdjecie2']['name']) ? file_get_contents($_FILES['zdjecie2']['tmp_name']) : null;
        $zdjecie3 = !empty($_FILES['zdjecie3']['name']) ? file_get_contents($_FILES['zdjecie3']['tmp_name']) : null;

        $sql = "INSERT INTO polowy (uzytkownik, nazwa_ryby, waga, rozmiar, miejscowka, data, zdj1, zdj2, zdj3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Przypisanie null dla obrazów, które nie zostały przesłane
        $null = null;
        $stmt->bind_param("ssddssbbb", $uzytkownik, $nazwaRyby, $waga, $rozmiar, $miejscowka, $data, $zdjecie1, $zdjecie2, $zdjecie3);

        // Dla pól BLOB trzeba użyć bind_param z 'b' i następnie mysqli_stmt_send_long_data
        $stmt->send_long_data(6, $zdjecie1);
        if ($zdjecie2 !== null) {
            $stmt->send_long_data(7, $zdjecie2);
        }
        if ($zdjecie3 !== null) {
            $stmt->send_long_data(8, $zdjecie3);
        }
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
    Wybierz złapaną rybę: <select name="nazwaRyby" required>
        <?php foreach ($ryby as $nazwaRyby): ?>
            <option value="<?php echo htmlspecialchars($nazwaRyby); ?>"><?php echo htmlspecialchars($nazwaRyby); ?></option>
        <?php endforeach; ?>
    </select><br>
    <input type="number" step="0.01" name="waga" placeholder="Waga ryby [kg]" required><br>
    <input type="number" name="rozmiar" placeholder="Rozmiar ryby [cm]"><br>
    <input type="text" name="miejscowka" placeholder="Miejscówka"><br>
    <input type="date" name="data" required><br>
    Zdjęcie (wymagane): <input type="file" name="zdjecie1" id="zdj1" required><br>
    Opcjonalne zdjęcie 2: <input type="file" name="zdjecie2" id="zdj2"><br>
    Opcjonalne zdjęcie 3: <input type="file" name="zdjecie3" id="zdj3"><br>
    <button type="submit" name="dodaj">Dodaj polów</button>
</form>


</body>
</html>
