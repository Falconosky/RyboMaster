<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tutaj powinna znajdować się logika weryfikująca dane logowania, np. sprawdzanie w bazie danych
    $login = $_POST['login'];
    $pass = $_POST['password'];
    if (isset($_POST['dodaj'])) {
        include 'connect_database.php';

        // Sprawdź, czy użytkownik już istnieje w bazie danych
        $sqlCheckUser = "SELECT COUNT(*) FROM uzytkownicy WHERE nick = ?";
        $stmtCheckUser = $conn->prepare($sqlCheckUser);
        $stmtCheckUser->bind_param("s", $login);
        $stmtCheckUser->execute();
        $stmtCheckUser->bind_result($countUser);
        $stmtCheckUser->fetch();
        $stmtCheckUser->close();

        if ($countUser > 0) {
            $sql = "SELECT password FROM uzytkownicy WHERE nick = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();
            if ($pass === $row['password']) {
                $_SESSION['user'] = $login;
                header("Location: index.php");
                exit();
            } else {
                echo "Niepoprawne hasło.";
            }
        } else {
            // Przygotuj i wykonaj zapytanie do dodania nowego użytkownika
            $sqlInsertUser = "INSERT INTO uzytkownicy (nick, vis_nick, password) VALUES (?, ?, ?)";
            $stmtInsertUser = $conn->prepare($sqlInsertUser);
            $stmtInsertUser->bind_param("sss", $login, $login, $pass); // Używamy $user zarówno dla 'nick', jak i 'vis_nick'
            $stmtInsertUser->execute();

            if ($stmtInsertUser->affected_rows > 0) {
                // Jeśli weryfikacja jest pomyślna, ustaw dane użytkownika w sesji
                $_SESSION['user'] = $login;

                // Przekieruj do index.php
                header("Location: index.php");
                exit();
            }
            $stmtInsertUser->close();
        }

        include 'close_database.php';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>RyboMaster</title>
</head>
<body>

<form method="post" action="login.php">
    <input type="text" name="login" placeholder="Login">
    <input type="text" name="password" placeholder="Password">
    <button type="submit" name="dodaj">Zaloguj</button>
</form>

</body>
</html>