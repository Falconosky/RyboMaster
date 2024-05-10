<?php
session_start();
if (!isset($_SESSION['user'])) {
    // Użytkownik nie jest zalogowany, przekieruj do login.php
    header("Location: login.php");
}
include 'connect_database.php';

// Obsługa resetowania hasła
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resetPassword'])) {
    $nickToReset = $_POST['nickToReset'];
    $sqlUpdate = "UPDATE uzytkownicy SET password = '123' WHERE nick = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("s", $nickToReset);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}

// Obsługa zmiany poziomu uprawnień
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['changePermission'])) {
    $nickToChange = $_POST['nickToChange'];
    $newPermission = $_POST['newPermission'];
    $sqlUpdatePerm = "UPDATE uzytkownicy SET permission = ? WHERE nick = ?";
    $stmtUpdatePerm = $conn->prepare($sqlUpdatePerm);
    $stmtUpdatePerm->bind_param("is", $newPermission, $nickToChange);
    $stmtUpdatePerm->execute();
    $stmtUpdatePerm->close();
}

// Obsługa usuwania użytkownika
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteUser'])) {
    $nickToDelete = $_POST['nickToDelete'];
    // Zapytanie SQL do usunięcia użytkownika
    $sqlDelete = "DELETE FROM uzytkownicy WHERE nick = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("s", $nickToDelete);
    if (!$stmtDelete->execute()) {
        echo "<script>alert('Nie udało się usunąć użytkownika.');</script>";
    }
    $stmtDelete->close();
}

// Obsługa zmiany statusu weryfikacji na zweryfikowany
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verifyUser'])) {
    $nickToVerify = $_POST['nickToVerify'];
    // Zapytanie SQL do zmiany statusu weryfikacji na true
    $sqlVerify = "UPDATE uzytkownicy SET verified = 1 WHERE nick = ?";
    $stmtVerify = $conn->prepare($sqlVerify);
    $stmtVerify->bind_param("s", $nickToVerify);
    if (!$stmtVerify->execute()) {
        echo "<script>alert('Nie udało się zmienić statusu weryfikacji użytkownika.');</script>";
    }
    $stmtVerify->close();
}

$sql = "SELECT nick, vis_nick, password, permission, verified FROM uzytkownicy ORDER BY verified DESC, nick ASC";
$result = $conn->query($sql);

// Przygotowanie danych podzielonych na zweryfikowanych i niezweryfikowanych
$verifiedUsers = [];
$unverifiedUsers = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['verified']) {
            $verifiedUsers[] = $row;
        } else {
            $unverifiedUsers[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>RyboMaster - użytkownicy</title>
    <?php include 'header.php'; ?>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
<a href="index.php" id="img_start"><img src="img/start.png"/></a>
<h2>Użytkownicy zweryfikowani</h2>
<!-- Tabela dla zweryfikowanych użytkowników -->
<?php displayUsersTable($verifiedUsers); ?>

<h2>Użytkownicy niezweryfikowani</h2>
<!-- Tabela dla niezweryfikowanych użytkowników -->
<?php displayUsersTableUnverified($unverifiedUsers); ?>

<?php
function displayUsersTable($users) {
    if (count($users) > 0) {
        echo "<table>
                <tr><th>Nick</th><th>Vis_nick</th><th>Hasło</th><th>Aktualny poziom prawnień</th><th>Zmień poziom prawnień</th><th>Usuń</th></tr>";
        foreach ($users as $row) {
            echo "<tr>
                    <td>".$row["nick"]."</td>
                    <td>".$row["vis_nick"]."</td>
                    <td>
                        <form method='post' action=''>
                            <input type='hidden' name='nickToReset' value='".$row["nick"]."'>
                            <button type='submit' name='resetPassword'>Zresetuj hasło</button>
                        </form>
                    </td>
                    <td>".$row["permission"]."</td>
                    <td>";
                        if($row['permission'] == 5) {
                            echo "Zablokowane";
                        } else {
                            echo "<form method='post' action=''>
                                    <input type='hidden' name='nickToChange' value='".$row["nick"]."'>
                                    <select name='newPermission'>";
                                    for ($i = 0; $i <= 4; $i++) {
                                        echo "<option value='$i' ".($row['permission'] == $i ? 'selected' : '').">$i</option>";
                                    }
                            echo "</select>
                                    <button type='submit' name='changePermission'>Zmień</button>
                                </form>";
                        }
                    echo "</td>";
                    echo "<td>
                        <form method='post' action='' onsubmit='return confirm(\"Czy na pewno chcesz usunąć tego użytkownika?\");'>
                            <input type='hidden' name='nickToDelete' value='".$row["nick"]."'>
                            <button type='submit' name='deleteUser'>Usuń</button>
                        </form>
                    </td>
                    </tr>";
        }
                
        echo "</table>";
    } else {
        echo "<p>Brak użytkowników w tej kategorii.</p>";
    }
}

function displayUsersTableUnverified($users) {
    if (count($users) > 0) {
        echo "<table>
                <tr><th>Nick</th><th>Vis_nick</th><th>Hasło</th><th>Aktualny poziom prawnień</th><th>Zmień poziom prawnień</th><th>Usuń</th><th>Zweryfikuj</th></tr>";
        foreach ($users as $row) {
            echo "<tr>
                    <td>".$row["nick"]."</td>
                    <td>".$row["vis_nick"]."</td>
                    <td>
                        <form method='post' action=''>
                            <input type='hidden' name='nickToReset' value='".$row["nick"]."'>
                            <button type='submit' name='resetPassword'>Zresetuj hasło</button>
                        </form>
                    </td>
                    <td>".$row["permission"]."</td>
                    <td>";
                        if($row['permission'] == 5) {
                            echo "Zablokowane";
                        } else {
                            echo "<form method='post' action=''>
                                    <input type='hidden' name='nickToChange' value='".$row["nick"]."'>
                                    <select name='newPermission'>";
                                    for ($i = 0; $i <= 4; $i++) {
                                        echo "<option value='$i' ".($row['permission'] == $i ? 'selected' : '').">$i</option>";
                                    }
                            echo "</select>
                                    <button type='submit' name='changePermission'>Zmień</button>
                                </form>";
                        }
                    echo "</td>";
                    echo "<td>
                        <form method='post' action='' onsubmit='return confirm(\"Czy na pewno chcesz usunąć tego użytkownika?\");'>
                            <input type='hidden' name='nickToDelete' value='".$row["nick"]."'>
                            <button type='submit' name='deleteUser'>Usuń</button>
                        </form>
                    </td>";
                    echo "<td>
                        <form method='post' action='' onsubmit='return confirm(\"Czy na pewno chcesz zmienić status weryfikacji tego użytkownika?\");'>
                            <input type='hidden' name='nickToVerify' value='".$row["nick"]."'>
                            <button type='submit' name='verifyUser'>Zweryfikuj</button>
                        </form>
                    </td>
                    </tr>";
        }
                
        echo "</table>";
    } else {
        echo "<p>Brak użytkowników w tej kategorii.</p>";
    }
}
?>

</body>
</html>

<?php
$conn->close();
?>