<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] != 'veterinario') {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id'];

$sql = "SELECT u.nome,u.email,u.telefone,v.crmv,v.especialidade
        FROM usuarios u
        INNER JOIN veterinarios v ON v.id_usuario = u.id
        WHERE u.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$veterinario = mysqli_fetch_assoc($resultado);

if (!$veterinario) {
    die("Perfil do veterinário não encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Veterinário</title>
</head>
<body>
    <nav>
        <a href="home_veterinario.php">Início</a>
        <a href="agenda_veterinario.php">Agenda</a>
        <a href="vacina.php">Vacina</a>
        <a href="atendimento.php">Atendimentos</a>
        <a href="historico_animal.php">Animais</a>
        <a href="prescricao.php">Prescrição</a>
        <a href="perfil_veterinario.php">Perfil</a>
        <a href="../logout.php">Sair</a>
    </nav>
    <h1>Perfil do Veterinário</h1>
    <p>Nome: <?php echo htmlspecialchars($veterinario['nome']); ?></p>
    <p>Email: <?php echo htmlspecialchars($veterinario['email']); ?></p>
    <p>Telefone: <?php echo htmlspecialchars($veterinario['telefone'] ?? 'Não informado'); ?></p>
    <p>CRMV: <?php echo htmlspecialchars($veterinario['crmv']); ?></p>
    <p>Especialidade: <?php echo htmlspecialchars($veterinario['especialidade'] ?? 'Não informada'); ?></p>
    <a href="home_veterinario.php">Voltar</a>
</body>
</html>