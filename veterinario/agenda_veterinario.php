<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['tipo_usuario'] != 'veterinario') {
    header("Location: ../index.php");
    exit;
}

$id_usuario = $_SESSION['id'];

$sql_veterinario = "SELECT id FROM veterinarios WHERE id_usuario = ?";
$stmt = mysqli_prepare($conn, $sql_veterinario);
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$resultado_veterinario = mysqli_stmt_get_result($stmt);
$veterinario = mysqli_fetch_assoc($resultado_veterinario);

if (!$veterinario) {
    die("Veterinário não encontrado.");
}

$id_veterinario = $veterinario['id'];

$sql_hoje = "SELECT a.id, a.data, a.hora, a.status, a.observacoes,
             an.nome AS animal, u.nome AS tutor, s.nome AS servico
             FROM agendamentos a
             INNER JOIN animais an ON a.id_animal = an.id
             INNER JOIN usuarios u ON an.id_tutor = u.id
             INNER JOIN servicos s ON a.id_servico = s.id
             WHERE a.id_veterinario = ?
             AND a.data = CURDATE()
             ORDER BY a.hora ASC";

$stmt = mysqli_prepare($conn, $sql_hoje);
mysqli_stmt_bind_param($stmt, "i", $id_veterinario);
mysqli_stmt_execute($stmt);
$resultado_hoje = mysqli_stmt_get_result($stmt);

$sql_semana = "SELECT a.id, a.data, a.hora, a.status, a.observacoes,
               an.nome AS animal, u.nome AS tutor, s.nome AS servico
               FROM agendamentos a
               INNER JOIN animais an ON a.id_animal = an.id
               INNER JOIN usuarios u ON an.id_tutor = u.id
               INNER JOIN servicos s ON a.id_servico = s.id
               WHERE a.id_veterinario = ?
               AND a.data BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 DAY)
               ORDER BY a.data ASC, a.hora ASC";

$stmt = mysqli_prepare($conn, $sql_semana);
mysqli_stmt_bind_param($stmt, "i", $id_veterinario);
mysqli_stmt_execute($stmt);
$resultado_semana = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - PETVIDA</title>
</head>
<body>
<header>
    <h2>PETVIDA</h2>
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
</header>

<main>
    <h1>Agenda do Veterinário</h1>

    <section>
        <h2>Agenda do Dia</h2>

        <?php if (mysqli_num_rows($resultado_hoje) > 0): ?>
            <?php while ($agendamento = mysqli_fetch_assoc($resultado_hoje)): ?>
                <article>
                    <p><strong>Horário:</strong> <?php echo date("H:i", strtotime($agendamento['hora'])); ?></p>
                    <p><strong>Animal:</strong> <?php echo htmlspecialchars($agendamento['animal']); ?></p>
                    <p><strong>Tutor:</strong> <?php echo htmlspecialchars($agendamento['tutor']); ?></p>
                    <p><strong>Serviço:</strong> <?php echo htmlspecialchars($agendamento['servico']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($agendamento['status']); ?></p>
                    <?php if (!empty($agendamento['observacoes'])): ?>
                        <p><strong>Observações:</strong> <?php echo nl2br(htmlspecialchars($agendamento['observacoes'])); ?></p>
                    <?php endif; ?>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum atendimento agendado para hoje.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Agenda da Semana</h2>

        <?php if (mysqli_num_rows($resultado_semana) > 0): ?>
            <?php while ($agendamento = mysqli_fetch_assoc($resultado_semana)): ?>
                <article>
                    <p><strong>Data:</strong> <?php echo date("d/m/Y", strtotime($agendamento['data'])); ?></p>
                    <p><strong>Horário:</strong> <?php echo date("H:i", strtotime($agendamento['hora'])); ?></p>
                    <p><strong>Animal:</strong> <?php echo htmlspecialchars($agendamento['animal']); ?></p>
                    <p><strong>Tutor:</strong> <?php echo htmlspecialchars($agendamento['tutor']); ?></p>
                    <p><strong>Serviço:</strong> <?php echo htmlspecialchars($agendamento['servico']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($agendamento['status']); ?></p>
                    <?php if (!empty($agendamento['observacoes'])): ?>
                        <p><strong>Observações:</strong> <?php echo nl2br(htmlspecialchars($agendamento['observacoes'])); ?></p>
                    <?php endif; ?>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum atendimento encontrado para os próximos dias.</p>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>