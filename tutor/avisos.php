<?php
include "../conexao.php";
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Avisos</h1>
    <?php
    $sql = "SELECT * FROM avisos";
    $result = mysqli_query($conexao, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div>";
            echo "<h2>" . $row['titulo'] . "</h2>";
            echo "<p>" . $row['mensagem'] . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Nenhum aviso encontrado.</p>";
    }
</body>
</html>