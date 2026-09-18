<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] != 'veterinario') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Prontuário inválido.");
}

$id_prontuario = intval($_GET['id']);
$id_usuario = $_SESSION['id'];

$sql = "SELECT p.id,a.id_animal,an.nome AS animal,u.nome AS tutor
        FROM prontuarios p
        INNER JOIN agendamentos a ON a.id = p.id_agendamento
        INNER JOIN animais an ON an.id = a.id_animal
        INNER JOIN usuarios u ON u.id = an.id_tutor
        INNER JOIN veterinarios v ON v.id = a.id_veterinario
        WHERE p.id = ? AND v.id_usuario = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id_prontuario, $id_usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$prontuario = mysqli_fetch_assoc($resultado);

if (!$prontuario) {
    die("Prontuário não encontrado.");
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicamento = trim($_POST['medicamento']);
    $dosagem = trim($_POST['dosagem']);
    $periodo = trim($_POST['periodo']);
    $instrucoes = trim($_POST['instrucoes']);

    if ($medicamento == "" || $dosagem == "" || $periodo == "") {
        $mensagem = "Preencha os campos obrigatórios.";
    } else {
        $sql = "INSERT INTO prescricoes (id_prontuario,medicamento,dosagem,periodo_tratamento,instrucoes)
                VALUES (?,?,?,?,?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issss", $id_prontuario, $medicamento, $dosagem, $periodo, $instrucoes);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: prontuarios.php?prescricao=sucesso");
            exit;
        }

        $mensagem = "Erro ao cadastrar a prescrição.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescrição</title>
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
    <h1>Prescrição</h1>
    <p>Animal: <?php echo htmlspecialchars($prontuario['animal']); ?></p>
    <p>Tutor: <?php echo htmlspecialchars($prontuario['tutor']); ?></p>

    <?php if ($mensagem): ?>
        <p><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="medicamento">Medicamento:</label>
        <input type="text" id="medicamento" name="medicamento" required>

        <label for="dosagem">Dosagem:</label>
        <input type="text" id="dosagem" name="dosagem" required>

        <label for="periodo">Período:</label>
        <input type="text" id="periodo" name="periodo" required>

        <label for="instrucoes">Instruções:</label>
        <textarea id="instrucoes" name="instrucoes"></textarea>

        <button type="submit">Enviar Prescrição</button>
    </form>

    <a href="prontuarios.php">Voltar</a>
</body>
</html>