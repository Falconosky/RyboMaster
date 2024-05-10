<?php
session_start();
if (!isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: login.php");
}
include 'connect_database.php';

$user = $_SESSION['user'] ?? ''; // Pobranie loginu użytkownika z sesji

// Pobranie danych użytkownika z bazy
$query = $conn->prepare("SELECT vis_nick, password FROM uzytkownicy WHERE nick = ?");
$query->bind_param("s", $user);
$query->execute();
$result = $query->get_result();
$userData = $result->fetch_assoc();

// Aktualizacja hasła
if (isset($_POST['zmienHaslo']) && !empty($_POST['noweHaslo'])) {
    $noweHaslo = $_POST['noweHaslo'];
    $updateQuery = $conn->prepare("UPDATE uzytkownicy SET password = ? WHERE nick = ?");
    $updateQuery->bind_param("ss", $noweHaslo, $user);
    $updateQuery->execute();
    echo "Hasło zostało zmienione.<br>";
}

// Aktualizacja vis_nick
if (isset($_POST['zmienNick']) && !empty($_POST['nowyNick'])) {
    $nowyNick = $_POST['nowyNick'];
    $updateNickQuery = $conn->prepare("UPDATE uzytkownicy SET vis_nick = ? WHERE nick = ?");
    $updateNickQuery->bind_param("ss", $nowyNick, $user);
    $updateNickQuery->execute();
    echo "Nick został zmieniony.<br>";
    $userData['vis_nick'] = $_POST['nowyNick'];
}

include 'close_database.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>RyboMaster - profil</title>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
    <?php include 'header.php'; ?>
</head>
<body>
<a href="index.php" id="img_start"><img src="img/start.png"/></a>
<h2>Profil użytkownika</h2>
<p>Login: <?php echo htmlspecialchars($user); ?></p>
<p>Nick: <?php echo htmlspecialchars($userData['vis_nick'] ?? ''); ?></p>
<form method="post">
    <input type="text" name="nowyNick" placeholder="nowy nick">
    <button type="submit" name="zmienNick">Zmień nick</button>
</form>

<form method="post">
    <input type="password" name="noweHaslo" placeholder="nowe hasło">
    <button type="submit" name="zmienHaslo">Zmień hasło</button>
</form>

</body>
</html>
