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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: agenda.php");
    exit;
}

$id_usuario = $_SESSION['id'];
$id_agendamento = $_GET['id'];

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

$sql_agendamento = "SELECT a.id, a.data, a.hora, a.status,
                    an.nome AS animal, u.nome AS tutor,
                    s.nome AS servico
                    FROM agendamentos a
                    INNER JOIN animais an ON a.id_animal = an.id
                    INNER JOIN usuarios u ON an.id_tutor = u.id
                    INNER JOIN servicos s ON a.id_servico = s.id
                    WHERE a.id = ? AND a.id_veterinario = ?";

$stmt = mysqli_prepare($conn, $sql_agendamento);
mysqli_stmt_bind_param($stmt, "ii", $id_agendamento, $id_veterinario);
mysqli_stmt_execute($stmt);
$resultado_agendamento = mysqli_stmt_get_result($stmt);
$agendamento = mysqli_fetch_assoc($resultado_agendamento);

if (!$agendamento) {
    header("Location: agenda.php");
    exit;
}

$sql_prontuario = "SELECT id FROM prontuarios WHERE id_agendamento = ?";
$stmt = mysqli_prepare($conn, $sql_prontuario);
mysqli_stmt_bind_param($stmt, "i", $id_agendamento);
mysqli_stmt_execute($stmt);
$resultado_prontuario = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado_prontuario) > 0) {
    header("Location: prontuarios.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $peso = !empty($_POST['peso']) ? $_POST['peso'] : null;
    $sintomas = trim($_POST['sintomas']);
    $diagnostico = trim($_POST['diagnostico']);
    $observacoes = trim($_POST['observacoes']);

    $sql = "INSERT INTO prontuarios
            (id_agendamento, peso, sintomas, diagnostico, observacoes)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "idsss", $id_agendamento, $peso, $sintomas, $diagnostico, $observacoes);

    if (mysqli_stmt_execute($stmt)) {
        $sql_status = "UPDATE agendamentos
                       SET status = 'realizado'
                       WHERE id = ? AND id_veterinario = ?";

        $stmt_status = mysqli_prepare($conn, $sql_status);
        mysqli_stmt_bind_param($stmt_status, "ii", $id_agendamento, $id_veterinario);
        mysqli_stmt_execute($stmt_status);

        header("Location: prontuarios.php?atendimento=sucesso");
        exit;
    }

    $erro = "Não foi possível finalizar o atendimento.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atendimento - PETVIDA</title>
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
    <h1>Atendimento</h1>

    <?php if (isset($erro)): ?>
        <p><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <section>
        <h2>Dados da Consulta</h2>
        <p><strong>Animal:</strong> <?php echo htmlspecialchars($agendamento['animal']); ?></p>
        <p><strong>Tutor:</strong> <?php echo htmlspecialchars($agendamento['tutor']); ?></p>
        <p><strong>Serviço:</strong> <?php echo htmlspecialchars($agendamento['servico']); ?></p>
        <p><strong>Data:</strong> <?php echo date("d/m/Y", strtotime($agendamento['data'])); ?></p>
        <p><strong>Horário:</strong> <?php echo date("H:i", strtotime($agendamento['hora'])); ?></p>
    </section>

    <form method="POST">
        <label for="peso">Peso (kg):</label>
        <input type="number" id="peso" name="peso" step="0.01" min="0">

        <label for="sintomas">Sintomas:</label>
        <textarea id="sintomas" name="sintomas" rows="4" required></textarea>

        <label for="diagnostico">Diagnóstico:</label>
        <textarea id="diagnostico" name="diagnostico" rows="4" required></textarea>

        <label for="observacoes">Observações:</label>
        <textarea id="observacoes" name="observacoes" rows="4"></textarea>

        <button type="submit">Finalizar Atendimento</button>
    </form>

    <a href="agenda.php">Voltar para agenda</a>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>