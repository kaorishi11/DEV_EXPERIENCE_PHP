<?php
include 'conexao.php';
session_start();
$mensagem = "";

if (isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso') {
    $mensagem = "Sucesso!";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) == 1) {
        $u = mysqli_fetch_assoc($resultado);

        if (password_verify($senha, $u['senha'])) {

            $_SESSION['id'] = $u['id'];
            $_SESSION['nome'] = $u['nome'];
            $_SESSION['email'] = $u['email'];
            $_SESSION['tipo_usuario'] = $u['tipo_usuario'];

            if ($u['tipo_usuario'] == 'administrador') {
                header("Location: adm/home_admin.php");
            } elseif ($u['tipo_usuario'] == 'veterinario') {
                header("Location: veterinario/home_veterinario.php");
            } else {
                header("Location: tutor/home_tutor.php");
            }
            exit;
        } else {
            $mensagem = "E-mail ou senha incorretos.";
        }
    } else {
        $mensagem = "E-mail ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PETVIDA</title>
</head>
<body>
    <div>
        <h1>Login</h1>
        <?php if ($mensagem != ""): ?>
            <p><?php echo htmlspecialchars($mensagem); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
            <br><br>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            <br><br>

            <button type="submit">Entrar</button>
        </form>
        <p>Não tem uma conta?<a href="cadastro.php">Cadastre-se</a></p>
    </div>
</body>
</html>