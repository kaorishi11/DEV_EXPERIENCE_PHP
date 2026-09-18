<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] != 'administrador') {
    header("Location: ../login.php");
    exit;
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? "";
    $id = intval($_POST['id'] ?? 0);
    $id_veterinario = intval($_POST['id_veterinario'] ?? 0);
    $dia_semana = $_POST['dia_semana'] ?? "";
    $hora_inicio = $_POST['hora_inicio'] ?? "";
    $hora_fim = $_POST['hora_fim'] ?? "";

    if ($id_veterinario <= 0 || $dia_semana == "" || $hora_inicio == "" || $hora_fim == "") {
        $mensagem = "Preencha todos os campos.";
    } elseif ($hora_inicio >= $hora_fim) {
        $mensagem = "A hora final deve ser maior que a hora inicial.";
    } else {
        if ($acao == "cadastrar") {
            $stmt = mysqli_prepare($conn, "INSERT INTO horarios_veterinarios (id_veterinario,dia_semana,hora_inicio,hora_fim) VALUES (?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "isss", $id_veterinario, $dia_semana, $hora_inicio, $hora_fim);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: horarios.php?sucesso=cadastrado");
                exit;
            }

            $mensagem = "Não foi possível cadastrar o horário.";
        }

        if ($acao == "editar") {
            $stmt = mysqli_prepare($conn, "UPDATE horarios_veterinarios SET id_veterinario=?,dia_semana=?,hora_inicio=?,hora_fim=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "isssi", $id_veterinario, $dia_semana, $hora_inicio, $hora_fim, $id);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: horarios.php?sucesso=editado");
                exit;
            }

            $mensagem = "Não foi possível editar o horário.";
        }
    }
}

if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $stmt = mysqli_prepare($conn, "DELETE FROM horarios_veterinarios WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: horarios.php?sucesso=excluido");
        exit;
    }

    $mensagem = "Não foi possível excluir o horário.";
}

$editar = null;

if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = mysqli_prepare($conn, "SELECT id,id_veterinario,dia_semana,hora_inicio,hora_fim FROM horarios_veterinarios WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado_editar = mysqli_stmt_get_result($stmt);
    $editar = mysqli_fetch_assoc($resultado_editar);
}

$veterinarios = mysqli_query($conn, "SELECT v.id,u.nome,v.crmv
                                     FROM veterinarios v
                                     INNER JOIN usuarios u ON u.id=v.id_usuario
                                     ORDER BY u.nome");

$resultado = mysqli_query($conn, "SELECT h.id,h.id_veterinario,h.dia_semana,h.hora_inicio,h.hora_fim,u.nome,v.crmv
                                   FROM horarios_veterinarios h
                                   INNER JOIN veterinarios v ON v.id=h.id_veterinario
                                   INNER JOIN usuarios u ON u.id=v.id_usuario
                                   ORDER BY u.nome,h.id");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horários - Administrador</title>
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

    <h1>Gerenciar Horários</h1>

    <?php if ($mensagem): ?>
        <p><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <p>Operação realizada com sucesso.</p>
    <?php endif; ?>

    <h2><?php echo $editar ? "Editar Horário" : "Cadastrar Horário"; ?></h2>

    <form method="POST">
        <input type="hidden" name="acao" value="<?php echo $editar ? 'editar' : 'cadastrar'; ?>">

        <?php if ($editar): ?>
            <input type="hidden" name="id" value="<?php echo $editar['id']; ?>">
        <?php endif; ?>

        <label>Veterinário:</label>
        <select name="id_veterinario" required>
            <option value="">Selecione</option>
            <?php while ($veterinario = mysqli_fetch_assoc($veterinarios)): ?>
                <option value="<?php echo $veterinario['id']; ?>" <?php echo (($editar['id_veterinario'] ?? '') == $veterinario['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($veterinario['nome']); ?> - CRMV <?php echo htmlspecialchars($veterinario['crmv']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Dia da semana:</label>
        <select name="dia_semana" required>
            <option value="">Selecione</option>
            <option value="segunda" <?php echo (($editar['dia_semana'] ?? '') == 'segunda') ? 'selected' : ''; ?>>Segunda-feira</option>
            <option value="terca" <?php echo (($editar['dia_semana'] ?? '') == 'terca') ? 'selected' : ''; ?>>Terça-feira</option>
            <option value="quarta" <?php echo (($editar['dia_semana'] ?? '') == 'quarta') ? 'selected' : ''; ?>>Quarta-feira</option>
            <option value="quinta" <?php echo (($editar['dia_semana'] ?? '') == 'quinta') ? 'selected' : ''; ?>>Quinta-feira</option>
            <option value="sexta" <?php echo (($editar['dia_semana'] ?? '') == 'sexta') ? 'selected' : ''; ?>>Sexta-feira</option>
            <option value="sabado" <?php echo (($editar['dia_semana'] ?? '') == 'sabado') ? 'selected' : ''; ?>>Sábado</option>
            <option value="domingo" <?php echo (($editar['dia_semana'] ?? '') == 'domingo') ? 'selected' : ''; ?>>Domingo</option>
        </select>

        <label>Hora inicial:</label>
        <input type="time" name="hora_inicio" value="<?php echo htmlspecialchars($editar['hora_inicio'] ?? ''); ?>" required>

        <label>Hora final:</label>
        <input type="time" name="hora_fim" value="<?php echo htmlspecialchars($editar['hora_fim'] ?? ''); ?>" required>

        <button type="submit"><?php echo $editar ? "Salvar alterações" : "Cadastrar horário"; ?></button>

        <?php if ($editar): ?>
            <a href="horarios.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Horários cadastrados</h2>

    <table border="1">
        <tr>
            <th>Veterinário</th>
            <th>CRMV</th>
            <th>Dia da semana</th>
            <th>Hora inicial</th>
            <th>Hora final</th>
            <th>Ações</th>
        </tr>

        <?php while ($horario = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo htmlspecialchars($horario['nome']); ?></td>
                <td><?php echo htmlspecialchars($horario['crmv']); ?></td>
                <td><?php echo htmlspecialchars($horario['dia_semana']); ?></td>
                <td><?php echo htmlspecialchars(substr($horario['hora_inicio'], 0, 5)); ?></td>
                <td><?php echo htmlspecialchars(substr($horario['hora_fim'], 0, 5)); ?></td>
                <td>
                    <a href="horarios.php?editar=<?php echo $horario['id']; ?>">Editar</a>
                    <a href="horarios.php?excluir=<?php echo $horario['id']; ?>" onclick="return confirm('Deseja excluir este horário?')">Excluir</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>