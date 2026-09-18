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
    <h1>Cadastro de Animal</h1>
    <form>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="especie">Espécie:</label>
        <input type="text" id="especie" name="especie" required>

        <label for="raca">Raça:</label>
        <input type="text" id="raca" name="raca" required>

        <label for="sexo">Sexo:</label>
        <select id="sexo" name="sexo" required>
            <option value="M">Macho</option>
            <option value="F">Fêmea</option>
        </select>

        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento" required>

        <label for="foto">Foto:</label>
        <input type="file" id="foto" name="foto">

        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>