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

$sql_hoje = "SELECT a.id, a.hora, a.status, an.nome AS animal,
             s.nome AS servico, u.nome AS tutor
             FROM agendamentos a
             INNER JOIN animais an ON a.id_animal = an.id
             INNER JOIN usuarios u ON an.id_tutor = u.id
             INNER JOIN servicos s ON a.id_servico = s.id
             WHERE a.id_veterinario = ? AND a.data = CURDATE()
             ORDER BY a.hora ASC";

$stmt = mysqli_prepare($conn, $sql_hoje);
mysqli_stmt_bind_param($stmt, "i", $id_veterinario);
mysqli_stmt_execute($stmt);
$resultado_hoje = mysqli_stmt_get_result($stmt);

$sql_proximos = "SELECT a.id, a.data, a.hora, a.status, an.nome AS animal,
                 s.nome AS servico
                 FROM agendamentos a
                 INNER JOIN animais an ON a.id_animal = an.id
                 INNER JOIN servicos s ON a.id_servico = s.id
                 WHERE a.id_veterinario = ?
                 AND (a.data > CURDATE() OR (a.data = CURDATE() AND a.hora >= CURTIME()))
                 AND a.status IN ('agendado','confirmado')
                 ORDER BY a.data ASC, a.hora ASC
                 LIMIT 5";

$stmt = mysqli_prepare($conn, $sql_proximos);
mysqli_stmt_bind_param($stmt, "i", $id_veterinario);
mysqli_stmt_execute($stmt);
$resultado_proximos = mysqli_stmt_get_result($stmt);

$sql_total = "SELECT COUNT(*) AS total
              FROM agendamentos
              WHERE id_veterinario = ?";

$stmt = mysqli_prepare($conn, $sql_total);
mysqli_stmt_bind_param($stmt, "i", $id_veterinario);
mysqli_stmt_execute($stmt);
$resultado_total = mysqli_stmt_get_result($stmt);
$total = mysqli_fetch_assoc($resultado_total);

$sql_vacinas = "SELECT COUNT(*) AS total
                FROM vacinacoes
                WHERE id_veterinario = ?";

$stmt = mysqli_prepare($conn, $sql_vacinas);
mysqli_stmt_bind_param($stmt, "i", $id_veterinario);
mysqli_stmt_execute($stmt);
$resultado_vacinas = mysqli_stmt_get_result($stmt);
$total_vacinas = mysqli_fetch_assoc($resultado_vacinas);

$sql_dados = "SELECT u.nome, v.crmv, v.especialidade
              FROM veterinarios v
              INNER JOIN usuarios u ON v.id_usuario = u.id
              WHERE v.id = ?";

$stmt = mysqli_prepare($conn, $sql_dados);
mysqli_stmt_bind_param($stmt, "i", $id_veterinario);
mysqli_stmt_execute($stmt);
$resultado_dados = mysqli_stmt_get_result($stmt);
$dados = mysqli_fetch_assoc($resultado_dados);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início - Veterinário - PETVIDA</title>
</head>
<body>
<header>
    <h2>PETVIDA</h2>
    <p>Olá, Dr(a). <?php echo htmlspecialchars($dados['nome']); ?></p>
    <nav>
        <a href="home_veterinario.php">Início</a>
        <a href="agenda.php">Agenda</a>
        <a href="animais.php">Animais</a>
        <a href="prontuarios.php">Prontuários</a>
        <a href="vacinas.php">Vacinas</a>
        <a href="horarios.php">Horários</a>
        <a href="../logout.php">Sair</a>
    </nav>
</header>

<main>
    <h1>Bem-vindo ao sistema do veterinário!</h1>

    <section>
        <h2>Resumo</h2>
        <div>
            <h3>Atendimentos do dia</h3>
            <p><?php echo mysqli_num_rows($resultado_hoje); ?></p>
        </div>
        <div>
            <h3>Total de consultas</h3>
            <p><?php echo $total['total']; ?></p>
        </div>
        <div>
            <h3>Vacinas aplicadas</h3>
            <p><?php echo $total_vacinas['total']; ?></p>
        </div>
    </section>

    <section>
        <h2>Atendimentos de Hoje</h2>

        <?php if (mysqli_num_rows($resultado_hoje) > 0): ?>
            <?php while ($atendimento = mysqli_fetch_assoc($resultado_hoje)): ?>
                <article>
                    <p><strong>Horário:</strong> <?php echo date("H:i", strtotime($atendimento['hora'])); ?></p>
                    <p><strong>Animal:</strong> <?php echo htmlspecialchars($atendimento['animal']); ?></p>
                    <p><strong>Tutor:</strong> <?php echo htmlspecialchars($atendimento['tutor']); ?></p>
                    <p><strong>Serviço:</strong> <?php echo htmlspecialchars($atendimento['servico']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($atendimento['status']); ?></p>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum atendimento agendado para hoje.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Próximos Horários</h2>

        <?php if (mysqli_num_rows($resultado_proximos) > 0): ?>
            <?php while ($proximo = mysqli_fetch_assoc($resultado_proximos)): ?>
                <article>
                    <p><strong>Data:</strong> <?php echo date("d/m/Y", strtotime($proximo['data'])); ?></p>
                    <p><strong>Horário:</strong> <?php echo date("H:i", strtotime($proximo['hora'])); ?></p>
                    <p><strong>Animal:</strong> <?php echo htmlspecialchars($proximo['animal']); ?></p>
                    <p><strong>Serviço:</strong> <?php echo htmlspecialchars($proximo['servico']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($proximo['status']); ?></p>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum próximo atendimento encontrado.</p>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>