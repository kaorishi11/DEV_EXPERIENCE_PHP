<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] != 'administrador') {
    header("Location: ../login.php");
    exit;
}

$mensagem = "";

if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $stmt = mysqli_prepare($conn, "DELETE FROM servicos WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: servicos.php?sucesso=excluido");
        exit;
    }

    $mensagem = "Não foi possível excluir o serviço. Ele pode estar vinculado a um agendamento.";
}

if (isset($_GET['alternar']) && is_numeric($_GET['alternar'])) {
    $id = intval($_GET['alternar']);
    $stmt = mysqli_prepare($conn, "UPDATE servicos SET ativo = NOT ativo WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: servicos.php?sucesso=alterado");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? "";

    if ($acao == "cadastrar") {
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $duracao = intval($_POST['duracao_minutos']);
        $valor = floatval($_POST['valor']);

        if ($nome == "" || $duracao <= 0 || $valor < 0) {
            $mensagem = "Preencha os campos corretamente.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO servicos (nome,descricao,duracao_minutos,valor,ativo) VALUES (?,?,?,?,TRUE)");
            mysqli_stmt_bind_param($stmt, "ssid", $nome, $descricao, $duracao, $valor);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: servicos.php?sucesso=cadastrado");
                exit;
            }

            $mensagem = "Não foi possível cadastrar o serviço.";
        }
    }

    if ($acao == "editar") {
        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $duracao = intval($_POST['duracao_minutos']);
        $valor = floatval($_POST['valor']);

        if ($nome == "" || $duracao <= 0 || $valor < 0) {
            $mensagem = "Preencha os campos corretamente.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE servicos SET nome=?,descricao=?,duracao_minutos=?,valor=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssidi", $nome, $descricao, $duracao, $valor, $id);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: servicos.php?sucesso=editado");
                exit;
            }

            $mensagem = "Não foi possível editar o serviço.";
        }
    }
}

$editar = null;

if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = mysqli_prepare($conn, "SELECT id,nome,descricao,duracao_minutos,valor,ativo FROM servicos WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado_editar = mysqli_stmt_get_result($stmt);
    $editar = mysqli_fetch_assoc($resultado_editar);
}

$resultado = mysqli_query($conn, "SELECT id,nome,descricao,duracao_minutos,valor,ativo FROM servicos ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - Administrador</title>
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

    <h1>Gerenciar Serviços</h1>

    <?php if ($mensagem): ?>
        <p><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <p>Operação realizada com sucesso.</p>
    <?php endif; ?>

    <h2><?php echo $editar ? "Editar Serviço" : "Cadastrar Serviço"; ?></h2>

    <form method="POST">
        <input type="hidden" name="acao" value="<?php echo $editar ? 'editar' : 'cadastrar'; ?>">

        <?php if ($editar): ?>
            <input type="hidden" name="id" value="<?php echo $editar['id']; ?>">
        <?php endif; ?>

        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($editar['nome'] ?? ''); ?>" required>

        <label>Descrição:</label>
        <textarea name="descricao"><?php echo htmlspecialchars($editar['descricao'] ?? ''); ?></textarea>

        <label>Duração em minutos:</label>
        <input type="number" name="duracao_minutos" min="1" value="<?php echo htmlspecialchars($editar['duracao_minutos'] ?? ''); ?>" required>

        <label>Valor:</label>
        <input type="number" name="valor" min="0" step="0.01" value="<?php echo htmlspecialchars($editar['valor'] ?? ''); ?>" required>

        <button type="submit"><?php echo $editar ? "Salvar alterações" : "Cadastrar"; ?></button>

        <?php if ($editar): ?>
            <a href="servicos.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Serviços cadastrados</h2>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Duração</th>
            <th>Valor</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>

        <?php while ($servico = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo $servico['id']; ?></td>
                <td><?php echo htmlspecialchars($servico['nome']); ?></td>
                <td><?php echo htmlspecialchars($servico['descricao'] ?? ''); ?></td>
                <td><?php echo $servico['duracao_minutos']; ?> min</td>
                <td>R$ <?php echo number_format($servico['valor'], 2, ',', '.'); ?></td>
                <td><?php echo $servico['ativo'] ? 'Ativo' : 'Inativo'; ?></td>
                <td>
                    <a href="servicos.php?editar=<?php echo $servico['id']; ?>">Editar</a>
                    <a href="servicos.php?alternar=<?php echo $servico['id']; ?>"><?php echo $servico['ativo'] ? 'Desativar' : 'Ativar'; ?></a>
                    <a href="servicos.php?excluir=<?php echo $servico['id']; ?>" onclick="return confirm('Deseja excluir este serviço?')">Excluir</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>