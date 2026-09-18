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

    $stmt = mysqli_prepare($conn, "SELECT id_usuario FROM veterinarios WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $veterinario = mysqli_fetch_assoc($resultado);

    if ($veterinario) {
        $id_usuario = $veterinario['id_usuario'];

        $stmt = mysqli_prepare($conn, "DELETE FROM usuarios WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id_usuario);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: veterinarios.php?sucesso=excluido");
            exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? "";

    if ($acao == "cadastrar") {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $senha = $_POST['senha'];
        $crmv = trim($_POST['crmv']);
        $especialidade = trim($_POST['especialidade']);

        if ($nome == "" || $email == "" || $senha == "" || $crmv == "") {
            $mensagem = "Preencha os campos obrigatórios.";
        } else {
            mysqli_begin_transaction($conn);

            try {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                $stmt = mysqli_prepare($conn, "INSERT INTO usuarios (nome,email,senha,telefone,tipo_usuario) VALUES (?,?,?,?, 'veterinario')");
                mysqli_stmt_bind_param($stmt, "ssss", $nome, $email, $senha_hash, $telefone);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Erro ao cadastrar usuário.");
                }

                $id_usuario = mysqli_insert_id($conn);

                $stmt = mysqli_prepare($conn, "INSERT INTO veterinarios (id_usuario,crmv,especialidade) VALUES (?,?,?)");
                mysqli_stmt_bind_param($stmt, "iss", $id_usuario, $crmv, $especialidade);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Erro ao cadastrar veterinário.");
                }

                mysqli_commit($conn);
                header("Location: veterinarios.php?sucesso=cadastrado");
                exit;
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $mensagem = "Não foi possível cadastrar. Verifique se o e-mail ou CRMV já está cadastrado.";
            }
        }
    }

    if ($acao == "editar") {
        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $crmv = trim($_POST['crmv']);
        $especialidade = trim($_POST['especialidade']);

        mysqli_begin_transaction($conn);

        try {
            $stmt = mysqli_prepare($conn, "SELECT id_usuario FROM veterinarios WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $veterinario = mysqli_fetch_assoc($resultado);

            if (!$veterinario) {
                throw new Exception("Veterinário não encontrado.");
            }

            $id_usuario = $veterinario['id_usuario'];

            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET nome=?,email=?,telefone=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssi", $nome, $email, $telefone, $id_usuario);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erro ao editar usuário.");
            }

            $stmt = mysqli_prepare($conn, "UPDATE veterinarios SET crmv=?,especialidade=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssi", $crmv, $especialidade, $id);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erro ao editar veterinário.");
            }

            mysqli_commit($conn);
            header("Location: veterinarios.php?sucesso=editado");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $mensagem = "Não foi possível editar. Verifique se o e-mail ou CRMV já está cadastrado.";
        }
    }
}

$editar = null;

if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);

    $stmt = mysqli_prepare($conn, "SELECT v.id,u.nome,u.email,u.telefone,v.crmv,v.especialidade
                                   FROM veterinarios v
                                   INNER JOIN usuarios u ON u.id=v.id_usuario
                                   WHERE v.id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $editar = mysqli_fetch_assoc($resultado);
}

$resultado = mysqli_query($conn, "SELECT v.id,u.nome,u.email,u.telefone,v.crmv,v.especialidade
                                   FROM veterinarios v
                                   INNER JOIN usuarios u ON u.id=v.id_usuario
                                   ORDER BY v.id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinários - Administrador</title>
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

    <h1>Gerenciar Veterinários</h1>

    <?php if ($mensagem): ?>
        <p><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <p>Operação realizada com sucesso.</p>
    <?php endif; ?>

    <h2><?php echo $editar ? "Editar Veterinário" : "Cadastrar Veterinário"; ?></h2>

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

        <label>CRMV:</label>
        <input type="text" name="crmv" value="<?php echo htmlspecialchars($editar['crmv'] ?? ''); ?>" required>

        <label>Especialidade:</label>
        <input type="text" name="especialidade" value="<?php echo htmlspecialchars($editar['especialidade'] ?? ''); ?>">

        <button type="submit"><?php echo $editar ? "Salvar alterações" : "Cadastrar"; ?></button>

        <?php if ($editar): ?>
            <a href="veterinarios.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Veterinários cadastrados</h2>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Telefone</th>
            <th>CRMV</th>
            <th>Especialidade</th>
            <th>Ações</th>
        </tr>

        <?php while ($veterinario = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo $veterinario['id']; ?></td>
                <td><?php echo htmlspecialchars($veterinario['nome']); ?></td>
                <td><?php echo htmlspecialchars($veterinario['email']); ?></td>
                <td><?php echo htmlspecialchars($veterinario['telefone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($veterinario['crmv']); ?></td>
                <td><?php echo htmlspecialchars($veterinario['especialidade'] ?? ''); ?></td>
                <td>
                    <a href="veterinarios.php?editar=<?php echo $veterinario['id']; ?>">Editar</a>
                    <a href="veterinarios.php?excluir=<?php echo $veterinario['id']; ?>" onclick="return confirm('Deseja excluir este veterinário?')">Excluir</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>