<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['tipo_usuario'] != 'tutor') {
    header("Location: ../index.php");
    exit;
}

$sql = "SELECT id, titulo, mensagem, data_publicacao
        FROM avisos
        WHERE ativo = TRUE
        ORDER BY data_publicacao DESC";

$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisos - PETVIDA</title>
</head>
<body>
<header>
    <h2>PETVIDA</h2>
    <nav>
        <a href="home_tutor.php">Início</a>
        <a href="agendamentos.php">Agendamento</a>
        <a href="meus_agendamentos.php">Meus Agendamentos</a>
        <a href="animais.php">Animais</a>
        <a href="avisos.php">Avisos</a>
        <a href="prontuario.php">Prontuários</a>
        <a href="vacinas.php">Vacinas</a>
        <a href="../logout.php">Sair</a>
    </nav>
</header>

<main>
    <h1>Avisos</h1>

    <?php if (mysqli_num_rows($resultado) > 0): ?>
        <?php while ($aviso = mysqli_fetch_assoc($resultado)): ?>
            <article>
                <h2><?php echo htmlspecialchars($aviso['titulo']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($aviso['mensagem'])); ?></p>
                <p><strong>Publicado em:</strong> <?php echo date("d/m/Y H:i", strtotime($aviso['data_publicacao'])); ?></p>
            </article>
            <hr>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhum aviso encontrado.</p>
    <?php endif; ?>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>