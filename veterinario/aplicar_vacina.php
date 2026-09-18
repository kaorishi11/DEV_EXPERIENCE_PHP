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
    <h1>Aplicar Vacina</h1>
    <form>
        <label for="animal">Animal:</label>
        <input type="text" id="animal" name="animal" required>

        <label for="vacina">Vacina:</label>
        <input type="text" id="vacina" name="vacina" required>

        <label for="lote">Lote:</label>
        <input type="text" id="lote" name="lote" required>

        <label for="data_aplicacao">Data de Aplicação:</label>
        <input type="date" id="data_aplicacao" name="data_aplicacao" required>

        <label for="proxima_dose">Próxima Dose:</label>
        <input type="date" id="proxima_dose" name="proxima_dose" required>

        <button type="submit">Aplicar Vacina</button>
    </form>
</body>
</html>