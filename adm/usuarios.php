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

    if ($id != $_SESSION['id']) {
        $stmt = mysqli_prepare($conn, "DELETE FROM usuarios WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        header("Location: usuarios.php?sucesso=excluido");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? "";

    if ($acao == "cadastrar") {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $senha = $_POST['senha'];
        $tipo = $_POST['tipo_usuario'];

        if ($nome == "" || $email == "" || $senha == "") {
            $mensagem = "Preencha os campos obrigatórios.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO usuarios (nome,email,senha,telefone,tipo_usuario) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "sssss", $nome, $email, $senha_hash, $telefone, $tipo);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: usuarios.php?sucesso=cadastrado");
                exit;
            }

            $mensagem = "Não foi possível cadastrar o usuário. Verifique se o e-mail já está cadastrado.";
        }
    }

    if ($acao == "editar") {
        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $tipo = $_POST['tipo_usuario'];

        $stmt = mysqli_prepare($conn, "UPDATE usuarios SET nome=?,email=?,telefone=?,tipo_usuario=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssssi", $nome, $email, $telefone, $tipo, $id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: usuarios.php?sucesso=editado");
            exit;
        }

        $mensagem = "Não foi possível editar o usuário.";
    }

    if ($acao == "senha") {
        $id = intval($_POST['id']);
        $senha = $_POST['senha'];
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "UPDATE usuarios SET senha=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $senha_hash, $id);
        mysqli_stmt_execute($stmt);

        header("Location: usuarios.php?sucesso=senha");
        exit;
    }
}

$editar = null;

if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);

    $stmt = mysqli_prepare($conn, "SELECT id,nome,email,telefone,tipo_usuario FROM usuarios WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $editar = mysqli_fetch_assoc($resultado);
}

$resultado = mysqli_query($conn, "SELECT id,nome,email,telefone,tipo_usuario,criado_em FROM usuarios ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Administrador</title>
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

    <h1>Gerenciar Usuários</h1>

    <?php if ($mensagem): ?>
        <p><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <p>Operação realizada com sucesso.</p>
    <?php endif; ?>

    <h2><?php echo $editar ? "Editar Usuário" : "Cadastrar Usuário"; ?></h2>

    <form method="POST">
        <input type="hidden" name="acao" value="<?php echo $editar ? 'editar' : 'cadastrar'; ?>">

        <?php if ($editar): ?>
            <input type="hidden" name="id" value="<?php echo $editar['id']; ?>">
        <?php endif; ?>

        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($editar['nome'] ?? ''); ?>" required>

        <label>E-mail:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($editar['email'] ?? ''); ?>" required>

        <label>Telefone:</label>
        <input type="text" name="telefone" value="<?php echo htmlspecialchars($editar['telefone'] ?? ''); ?>">

        <?php if (!$editar): ?>
            <label>Senha:</label>
            <input type="password" name="senha" required>
        <?php endif; ?>

        <label>Tipo de usuário:</label>
        <select name="tipo_usuario" required>
            <option value="tutor" <?php echo (($editar['tipo_usuario'] ?? '') == 'tutor') ? 'selected' : ''; ?>>Tutor</option>
            <option value="veterinario" <?php echo (($editar['tipo_usuario'] ?? '') == 'veterinario') ? 'selected' : ''; ?>>Veterinário</option>
            <option value="administrador" <?php echo (($editar['tipo_usuario'] ?? '') == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
        </select>

        <button type="submit"><?php echo $editar ? "Salvar alterações" : "Cadastrar"; ?></button>

        <?php if ($editar): ?>
            <a href="usuarios.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Lista de Usuários</h2>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Telefone</th>
            <th>Tipo</th>
            <th>Criado em</th>
            <th>Ações</th>
        </tr>

        <?php while ($usuario = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo $usuario['id']; ?></td>
                <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                <td><?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($usuario['tipo_usuario']); ?></td>
                <td><?php echo htmlspecialchars($usuario['criado_em']); ?></td>
                <td>
                    <a href="usuarios.php?editar=<?php echo $usuario['id']; ?>">Editar</a>
                    <?php if ($usuario['id'] != $_SESSION['id']): ?>
                        <a href="usuarios.php?excluir=<?php echo $usuario['id']; ?>" onclick="return confirm('Deseja excluir este usuário?')">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>