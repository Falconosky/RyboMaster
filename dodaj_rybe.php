<?php
if (isset($_POST['dodaj'])) {
    include 'connect_database.php'; // Załącz plik z połączeniem do bazy danych

    // Przygotuj zapytanie SQL do dodania rekordu
    $nazwaRyby = "leszcz";
    $sql = "INSERT INTO baza_ryb (nazwa) VALUES (?)";

    // Przygotuj i wykonaj zapytanie
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nazwaRyby); // "s" oznacza typ string
    $stmt->execute();

    $stmt->close();
    include 'close_database.php'; // Zamknij połączenie z bazą danych
}
?>
