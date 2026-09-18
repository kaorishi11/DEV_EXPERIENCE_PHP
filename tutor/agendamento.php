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
    <h1>Agendamento</h1>
    <form>
        <label for="animal">Animal:</label>
        <select id="animal" name="animal" required>
            <option value="cachorro">Cachorro</option>
            <option value="gato">Gato</option>
            <option value="passaro">Pássaro</option>
        </select>

        <label for="veterinario">Veterinário:</label>
        <select id="veterinario" name="veterinario" required>

        <label for="data">Data:</label>
        <input type="date" id="data" name="data" required>

        <label for="hora">Hora:</label>
        <input type="time" id="hora" name="hora" required>

        <label for="servico">Serviço:</label>
        <select id="servico" name="servico" required>
            <option value="consulta">Consulta</option>
            <option value="vacina">Vacina</option>
        </select>

        <button type="submit">Agendar</button>
    </form>
</body>
</html>