<?php
session_start(); // Rozpocznij sesję na początku pliku
?>

<!DOCTYPE html>
<html>
<head>
    <title>RyboMaster</title>
</head>
<body>

<form method="post" action="">
    User: <input type="text" name="user" value="<?php echo htmlspecialchars($_SESSION['user']); ?>" readonly>
    <input type="text" name="nazwaRyby" placeholder="Wpisz nazwę ryby">
    <button type="submit" name="dodaj">Dodaj rybę</button>
</form>

</body>
</html>