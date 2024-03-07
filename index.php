<?php
session_start(); // Rozpocznij sesję na początku pliku
?>

<!DOCTYPE html>
<html>
<head>
    <title>RyboMaster</title>
</head>
<body>

<?php

if (isset($_GET['logout'])) {
    // Usuń dane użytkownika z sesji
    unset($_SESSION['user']);
    // Zniszcz sesję
    session_destroy();
    // Przekieruj do strony logowania
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: login.php");
}

// Tutaj kod dla zalogowanych użytkowników
echo "Witaj, " . htmlspecialchars($_SESSION['user']) . "<br>" . "<br>";

include 'wyswietl_ryby.php';
include 'dodaj_rybe.php';
?>

<form method="post" action="">
    <input type="text" name="nazwaRyby" placeholder="Wpisz nazwę ryby">
    <button type="submit" name="dodaj">Dodaj rybę</button>
</form>
<br>
<form action="" method="post">
    <button type="submit" name="wyswietl">Wyswietl ryby</button>
</form>
<br>
<form action="dodaj_polow.php" method="post">
    <button type="submit" name="wyswietl">Dodaj połów</button>
</form>

<a href="?logout">Wyloguj</a>

</body>
</html>