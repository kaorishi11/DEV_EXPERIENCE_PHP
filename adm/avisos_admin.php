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
    $stmt = mysqli_prepare($conn, "DELETE FROM avisos WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: avisos_admin.php?sucesso=excluido");
        exit;
    }

    $mensagem = "Não foi possível excluir o aviso.";
}

if (isset($_GET['alternar']) && is_numeric($_GET['alternar'])) {
    $id = intval($_GET['alternar']);
    $stmt = mysqli_prepare($conn, "UPDATE avisos SET ativo=NOT ativo WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: avisos_admin.php?sucesso=alterado");
        exit;
    }

    $mensagem = "Não foi possível alterar o status do aviso.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? "";

    if ($acao == "cadastrar") {
        $titulo = trim($_POST['titulo']);
        $mensagem_aviso = trim($_POST['mensagem']);

        if ($titulo == "" || $mensagem_aviso == "") {
            $mensagem = "Preencha todos os campos obrigatórios.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO avisos (titulo,mensagem,ativo) VALUES (?,?,TRUE)");
            mysqli_stmt_bind_param($stmt, "ss", $titulo, $mensagem_aviso);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: avisos_admin.php?sucesso=cadastrado");
                exit;
            }

            $mensagem = "Não foi possível cadastrar o aviso.";
        }
    }

    if ($acao == "editar") {
        $id = intval($_POST['id']);
        $titulo = trim($_POST['titulo']);
        $mensagem_aviso = trim($_POST['mensagem']);

        if ($titulo == "" || $mensagem_aviso == "") {
            $mensagem = "Preencha todos os campos obrigatórios.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE avisos SET titulo=?,mensagem=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssi", $titulo, $mensagem_aviso, $id);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: avisos_admin.php?sucesso=editado");
                exit;
            }

            $mensagem = "Não foi possível editar o aviso.";
        }
    }
}

$editar = null;

if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = mysqli_prepare($conn, "SELECT id,titulo,mensagem,ativo FROM avisos WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado_editar = mysqli_stmt_get_result($stmt);
    $editar = mysqli_fetch_assoc($resultado_editar);
}

$resultado = mysqli_query($conn, "SELECT id,titulo,mensagem,data_publicacao,ativo FROM avisos ORDER BY data_publicacao DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisos - Administrador</title>
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

    <h1>Gerenciar Avisos</h1>

    <?php if ($mensagem): ?>
        <p><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <p>Operação realizada com sucesso.</p>
    <?php endif; ?>

    <h2><?php echo $editar ? "Editar Aviso" : "Criar Aviso"; ?></h2>

    <form method="POST">
        <input type="hidden" name="acao" value="<?php echo $editar ? 'editar' : 'cadastrar'; ?>">

        <?php if ($editar): ?>
            <input type="hidden" name="id" value="<?php echo $editar['id']; ?>">
        <?php endif; ?>

        <label>Título:</label>
        <input type="text" name="titulo" value="<?php echo htmlspecialchars($editar['titulo'] ?? ''); ?>" required>

        <label>Mensagem:</label>
        <textarea name="mensagem" required><?php echo htmlspecialchars($editar['mensagem'] ?? ''); ?></textarea>

        <button type="submit"><?php echo $editar ? "Salvar alterações" : "Publicar aviso"; ?></button>

        <?php if ($editar): ?>
            <a href="avisos_admin.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Avisos cadastrados</h2>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Mensagem</th>
            <th>Data de publicação</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>

        <?php while ($aviso = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo $aviso['id']; ?></td>
                <td><?php echo htmlspecialchars($aviso['titulo']); ?></td>
                <td><?php echo htmlspecialchars($aviso['mensagem']); ?></td>
                <td><?php echo htmlspecialchars($aviso['data_publicacao']); ?></td>
                <td><?php echo $aviso['ativo'] ? 'Ativo' : 'Inativo'; ?></td>
                <td>
                    <a href="avisos_admin.php?editar=<?php echo $aviso['id']; ?>">Editar</a>
                    <a href="avisos_admin.php?alternar=<?php echo $aviso['id']; ?>"><?php echo $aviso['ativo'] ? 'Desativar' : 'Publicar'; ?></a>
                    <a href="avisos_admin.php?excluir=<?php echo $aviso['id']; ?>" onclick="return confirm('Deseja excluir este aviso?')">Excluir</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>