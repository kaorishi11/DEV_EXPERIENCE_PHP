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

$sql_animais = "SELECT id, nome, especie
                FROM animais
                ORDER BY nome ASC";
$resultado_animais = mysqli_query($conn, $sql_animais);

$sql_vacinas = "SELECT id, nome, quantidade_estoque
                FROM vacinas
                WHERE ativo = TRUE
                ORDER BY nome ASC";
$resultado_vacinas = mysqli_query($conn, $sql_vacinas);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_animal = $_POST['id_animal'];
    $id_vacina = $_POST['id_vacina'];
    $data_aplicacao = $_POST['data_aplicacao'];
    $lote = trim($_POST['lote']);
    $data_proxima_dose = !empty($_POST['data_proxima_dose']) ? $_POST['data_proxima_dose'] : null;

    $sql_estoque = "SELECT quantidade_estoque
                    FROM vacinas
                    WHERE id = ? AND ativo = TRUE";
    $stmt = mysqli_prepare($conn, $sql_estoque);
    mysqli_stmt_bind_param($stmt, "i", $id_vacina);
    mysqli_stmt_execute($stmt);
    $resultado_estoque = mysqli_stmt_get_result($stmt);
    $vacina = mysqli_fetch_assoc($resultado_estoque);

    if (!$vacina) {
        $erro = "Vacina não encontrada.";
    } elseif ($vacina['quantidade_estoque'] <= 0) {
        $erro = "Esta vacina está sem estoque.";
    } else {
        $sql = "INSERT INTO vacinacoes
                (id_animal, id_vacina, id_veterinario, data_aplicacao, lote, data_proxima_dose)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiisss", $id_animal, $id_vacina, $id_veterinario, $data_aplicacao, $lote, $data_proxima_dose);

        if (mysqli_stmt_execute($stmt)) {
            $sql_estoque = "UPDATE vacinas
                            SET quantidade_estoque = quantidade_estoque - 1
                            WHERE id = ?";
            $stmt_estoque = mysqli_prepare($conn, $sql_estoque);
            mysqli_stmt_bind_param($stmt_estoque, "i", $id_vacina);
            mysqli_stmt_execute($stmt_estoque);

            header("Location: vacinas.php?aplicacao=sucesso");
            exit;
        }

        $erro = "Não foi possível registrar a aplicação da vacina.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicar Vacina - PETVIDA</title>
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
    <h1>Aplicar Vacina</h1>

    <?php if (isset($erro)): ?>
        <p><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="id_animal">Animal:</label>
        <select id="id_animal" name="id_animal" required>
            <option value="">Selecione o animal</option>
            <?php while ($animal = mysqli_fetch_assoc($resultado_animais)): ?>
                <option value="<?php echo $animal['id']; ?>">
                    <?php echo htmlspecialchars($animal['nome']); ?> - <?php echo htmlspecialchars(ucfirst($animal['especie'])); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="id_vacina">Vacina:</label>
        <select id="id_vacina" name="id_vacina" required>
            <option value="">Selecione a vacina</option>
            <?php while ($vacina = mysqli_fetch_assoc($resultado_vacinas)): ?>
                <option value="<?php echo $vacina['id']; ?>">
                    <?php echo htmlspecialchars($vacina['nome']); ?> - Estoque: <?php echo $vacina['quantidade_estoque']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="lote">Lote:</label>
        <input type="text" id="lote" name="lote" required>

        <label for="data_aplicacao">Data de Aplicação:</label>
        <input type="date" id="data_aplicacao" name="data_aplicacao" value="<?php echo date('Y-m-d'); ?>" required>

        <label for="data_proxima_dose">Próxima Dose:</label>
        <input type="date" id="data_proxima_dose" name="data_proxima_dose">

        <button type="submit">Aplicar Vacina</button>
    </form>

    <a href="vacinas.php">Voltar</a>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>