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
    <h1>Atendimento</h1>
    <form>
        <label for="animal">Animal:</label>
        <input type="text" id="animal" name="animal" required>

        <label for="dono">Dono:</label>
        <input type="text" id="dono" name="dono" required>

        <label for="peso">Peso:</label>
        <input type="text" id="peso" name="peso" required>

        <label for="sintomas">Sintomas:</label>
        <input type="text" id="sintomas" name="sintomas" required>

        <label for="diagnostico">Diagnóstico:</label>
        <input type="text" id="diagnostico" name="diagnostico" required>

        <label for="obs">Observações:</label>
        <input type="text" id="obs" name="obs" required>

        <button type="submit">Finalizar</button>
    </form>
</body>
</html>