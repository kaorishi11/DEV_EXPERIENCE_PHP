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

$id_tutor = $_SESSION['id'];

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_animal = intval($_POST['id_animal']);
    $id_veterinario = intval($_POST['id_veterinario']);
    $id_servico = intval($_POST['id_servico']);
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $observacoes = trim($_POST['observacoes'] ?? '');

    $sql_animal = "SELECT id
                   FROM animais
                   WHERE id = ?
                   AND id_tutor = ?";

    $stmt = mysqli_prepare($conn, $sql_animal);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $id_animal,
        $id_tutor
    );

    mysqli_stmt_execute($stmt);
    $resultado_animal = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($resultado_animal) == 0) {
        $erro = "Animal inválido.";
    } else {
        $sql_horario = "SELECT id
                        FROM agendamentos
                        WHERE id_veterinario = ?
                        AND data = ?
                        AND hora = ?
                        AND status IN ('agendado', 'confirmado')";
        $stmt = mysqli_prepare($conn, $sql_horario);
        mysqli_stmt_bind_param(
            $stmt,
            "iss",
            $id_veterinario,
            $data,
            $hora
        );

        mysqli_stmt_execute($stmt);
        $resultado_horario = mysqli_stmt_get_result($stmt);


        if (mysqli_num_rows($resultado_horario) > 0) {
            $erro = "Este horário já está ocupado. Escolha outro horário.";
        } else {
            $data_hora = strtotime($data . " " . $hora);
            if ($data_hora < time()) {
                $erro = "Não é possível agendar para uma data ou horário passado.";
            } else {
                $sql_insert = "INSERT INTO agendamentos
                               (
                                   id_animal,
                                   id_veterinario,
                                   id_servico,
                                   data,
                                   hora,
                                   observacoes
                               )
                               VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = mysqli_prepare($conn, $sql_insert);
                mysqli_stmt_bind_param(
                    $stmt,
                    "iiisss",
                    $id_animal,
                    $id_veterinario,
                    $id_servico,
                    $data,
                    $hora,
                    $observacoes
                );
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: meus_agendamentos.php");
                    exit;
                } else {
                    $erro = "Erro ao realizar o agendamento.";
                }
            }
        }
    }
}
$sql_animais = "SELECT
                    id,
                    nome,
                    especie,
                    raca
                FROM animais
                WHERE id_tutor = ?
                ORDER BY nome ASC";

$stmt_animais = mysqli_prepare($conn, $sql_animais);

mysqli_stmt_bind_param(
    $stmt_animais,
    "i",
    $id_tutor
);

mysqli_stmt_execute($stmt_animais);

$animais = mysqli_stmt_get_result($stmt_animais);

$sql_veterinarios = "SELECT
                        vet.id,
                        u.nome,
                        vet.crmv,
                        vet.especialidade
                     FROM veterinarios vet

                     INNER JOIN usuarios u
                        ON vet.id_usuario = u.id

                     WHERE u.tipo_usuario = 'veterinario'

                     ORDER BY u.nome ASC";

$resultado_veterinarios = mysqli_query(
    $conn,
    $sql_veterinarios
);

$sql_servicos = "SELECT
                    id,
                    nome,
                    descricao,
                    duracao_minutos,
                    valor
                 FROM servicos
                 WHERE ativo = TRUE
                 ORDER BY nome ASC";

$resultado_servicos = mysqli_query(
    $conn,
    $sql_servicos
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Agendamento - PETVIDA</title>
</head>
<body>
<header>
    <h2>PETVIDA</h2>
    <nav>
        <ul>
            <li><a href="home_tutor.php">Início</a>/li>
            <li><a href="agendamentos.php">Agendamento</a></li>
            <li><a href="meus_agendamentos.php">Meus Agendamentos</a></li>
            <li><a href="animais.php">Animais</a></li>
            <li><a href="avisos.php">Avisos</a></li>
            <li><a href="prontuario.php">Prontuários</a></li>
            <li><a href="vacinas.php">Vacinas</a></li>
            <li><a href="../logout.php">Sair</a></li>
        </ul>
    </nav>
</header>
<main>
    <h1>Novo Agendamento</h1>
    <p>Escolha o animal, veterinário, serviço, data e horário.</p>

    <?php if ($erro != ""): ?>
        <p><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="id_animal">Animal:</label>
        <select id="id_animal" name="id_animal" required>
            <option value="">Selecione o animal</option>
            <?php while ($animal = mysqli_fetch_assoc($animais)): ?>
                <option value="<?php echo $animal['id']; ?>">
                    <?php echo htmlspecialchars($animal['nome']); ?>
                    <?php echo htmlspecialchars($animal['especie']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label for="id_veterinario">Veterinário:</label>
        <select id="id_veterinario" name="id_veterinario" required>
            <option value="">Selecione o veterinário</option>
            <?php while ($veterinario = mysqli_fetch_assoc($resultado_veterinarios)): ?>
                <option
                    value="<?php echo $veterinario['id']; ?>"
                >
                    <?php echo htmlspecialchars($veterinario['nome']); ?>
                    CRMV:
                    <?php echo htmlspecialchars($veterinario['crmv']); ?>

                    <?php if (!empty($veterinario['especialidade'])): ?>
                        <?php echo htmlspecialchars($veterinario['especialidade']); ?>
                    <?php endif; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>
        <label for="id_servico">Serviço:</label>
        <select id="id_servico" name="id_servico" required>
            <option value="">Selecione o serviço</option>
            <?php while ($servico = mysqli_fetch_assoc($resultado_servicos)): ?>
                <option
                    value="<?php echo $servico['id']; ?>"
                >
                    <?php echo htmlspecialchars($servico['nome']); ?>
                    R$
                    <?php echo number_format(
                        $servico['valor'],
                        2,
                        ',',
                        '.'
                    ); ?>
                    <?php echo $servico['duracao_minutos']; ?> min
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label for="data">Data:</label>
        <input type="date" id="data" name="data" min="<?php echo date('Y-m-d'); ?>" required>
        <br><br>
        <label for="hora">Hora:</label>
        <input type="time" id="hora"  name="hora" required >

        <br><br>

        <label for="observacoes">Observações:</label>
        <br>

        <textarea
            id="observacoes"
            name="observacoes"
            rows="5"
            cols="40"
            placeholder="Digite alguma observação, se necessário..."
        ></textarea>

        <br><br>
        <button type="submit"></button>
    </form>
</main>
<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>