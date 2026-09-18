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
    <h1>Prescrição</h1>
    <form>
        <label for="medicamento">Medicamento:</label>
        <input type="text" id="medicamento" name="medicamento">

        <label for="dosagem">Dosagem:</label>
        <input type="text" id="dosagem" name="dosagem">

        <label for="periodo">Período:</label>
        <input type="text" id="periodo" name="periodo">

        <label for="instrucoes">Instruções:</label>
        <textarea id="instrucoes" name="instrucoes"></textarea>

        <button type="submit">Enviar Prescrição</button>
    </form>
</body>
</html>