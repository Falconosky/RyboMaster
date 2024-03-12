<?php
// Założenie jest takie, że ID i nazwa kolumny obrazu (img) są przekazywane przez URL, np. pokaz_zdjecie.php?id=123&img=zdj1

if (isset($_GET['id']) && isset($_GET['img'])) {
    include 'connect_database.php'; // Załączenie pliku do połączenia z bazą danych
    
    $id = intval($_GET['id']);
    $img = $conn->real_escape_string($_GET['img']);

    // Upewnij się, że nazwa kolumny jest prawidłowa, aby uniknąć iniekcji SQL
    $allowedCols = ['zdj1', 'zdj2', 'zdj3'];
    if (!in_array($img, $allowedCols)) {
        die("Nieprawidłowa nazwa kolumny obrazu.");
    }

    $query = $conn->prepare("SELECT $img FROM polowy WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $imageData = $row[$img];
        if (!empty($imageData)) {
            // Zakładając, że obrazy są w formacie WebP, możesz ustawić odpowiedni typ MIME
            header("Content-Type: image/webp");
            echo $imageData;
        } else {
            echo 'Obraz jest pusty lub nie istnieje.';
        }
    } else {
        echo 'Nie znaleziono obrazu.';
    }
    $query->close();
    $conn->close();
} else {
    echo 'Brak wymaganych parametrów.';
}
?>
