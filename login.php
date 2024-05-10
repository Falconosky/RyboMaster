<?php
session_start();
if (isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: index.php");
}

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
            $sql = "SELECT password, permission, verified FROM uzytkownicy WHERE nick = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $row = $result->fetch_assoc();
            if ($pass === $row['password']) {
                if ($row['verified']) { // Sprawdź, czy konto jest zweryfikowane
                    $_SESSION['user'] = $login;
                    $_SESSION['permission'] = $row['permission'];
                    header("Location: index.php");
                    exit();
                } else {
                    echo "Konto niezweryfikowane.<br>"; // Komunikat dla niezweryfikowanego konta
                }
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
                echo "Konto zostało stworzone. Zaczekaj na weryfikacje.<br>";
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
    <title>ZALOGUJ SIĘ!</title>
    <?php include 'header.php'; ?>
</head>
<body>
<div class="login_topka_info">Prosze nie logować się swoim "normalnym" hasłem</div>
<div class="login_form_log">
    <form method="post" action="login.php">
        <div class="login_linia"><div class="login_opis">Login: </div><input class="login_input" type="text" name="login" placeholder="Login"></div>
        <div class="login_linia"><div class="login_opis">Hasło: </div><input class="login_input" type="password" name="password" placeholder="Password"></div>
        <button class="login_button" type="submit" name="dodaj">Zaloguj</button>
    </form>
</div>

</body>
</html>