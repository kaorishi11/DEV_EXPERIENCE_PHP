<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] != 'administrador') {
    header("Location: ../login.php");
    exit;
}

$resultado = mysqli_query($conn, "SELECT COUNT(*) AS total FROM usuarios WHERE tipo_usuario='tutor'");
$tutores = mysqli_fetch_assoc($resultado)['total'];

$resultado = mysqli_query($conn, "SELECT COUNT(*) AS total FROM animais");
$animais = mysqli_fetch_assoc($resultado)['total'];

$resultado = mysqli_query($conn, "SELECT COUNT(*) AS total FROM veterinarios");
$veterinarios = mysqli_fetch_assoc($resultado)['total'];

$resultado = mysqli_query($conn, "SELECT COUNT(*) AS total FROM agendamentos");
$consultas = mysqli_fetch_assoc($resultado)['total'];

$resultado = mysqli_query($conn, "SELECT COALESCE(SUM(quantidade_estoque),0) AS total FROM vacinas WHERE ativo=TRUE");
$vacinas_estoque = mysqli_fetch_assoc($resultado)['total'];

$resultado = mysqli_query($conn, "SELECT COUNT(*) AS total
FROM vacinacoes
WHERE data_proxima_dose IS NOT NULL
AND data_proxima_dose < CURDATE()");
$vacinas_atraso = mysqli_fetch_assoc($resultado)['total'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início - Administrador</title>
</head>
<body>
    <div>
        <ul>
            <li><a href="home_admin.php">Início</a></li>
            <li><a href="usuarios.php">Usuários</a></li>
            <li><a href="veterinarios.php">Veterinários</a></li>
            <li><a href="horarios.php">Horários</a></li>
            <li><a href="servicos.php">Serviços</a></li>
            <li><a href="avisos_admin.php">Avisos</a></li>
            <li><a href="estoque.php">Estoque</a></li>
            <li><a href="relatorios.php">Relatórios</a></li>
            <li><a href="../logout.php">Sair</a></li>
        </ul>
    </div>

    <h1>Bem-vindo, Administrador</h1>

    <div>
        <h2>Total de tutores</h2>
        <p><?php echo $tutores; ?></p>
    </div>

    <div>
        <h2>Total de animais</h2>
        <p><?php echo $animais; ?></p>
    </div>

    <div>
        <h2>Veterinários</h2>
        <p><?php echo $veterinarios; ?></p>
    </div>

    <div>
        <h2>Consultas</h2>
        <p><?php echo $consultas; ?></p>
    </div>

    <div>
        <h2>Vacinas em estoque</h2>
        <p><?php echo $vacinas_estoque; ?></p>
    </div>

    <div>
        <h2>Vacinas em atraso</h2>
        <p><?php echo $vacinas_atraso; ?></p>
    </div>
</body>
</html>