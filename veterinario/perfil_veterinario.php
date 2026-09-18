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
    <h1>Perfil do Veterinário</h1>
    <p>Nome: <?php echo $_SESSION['nome']; ?></p>
    <p>Email: <?php echo $_SESSION['email']; ?></p>
    <p>Telefone: <?php echo $_SESSION['telefone']; ?></p>
    <p>Especialidade: <?php echo $_SESSION['especialidade']; ?></p>
</body>
</html>