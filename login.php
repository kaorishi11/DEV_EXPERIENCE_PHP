<?php
include 'conexao.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $r = $conn->query("SELECT * FROM usuarios WHERE email='$email' AND senha='$senha'");

    if ($r->num_rows == 1) {
        $u = $r->fetch_assoc();

        $_SESSION['id']    = $u['id'];
        $_SESSION['nome']  = $u['nome'];
        $_SESSION['tipo']  = $u['tipo'];

        if ($u['tipo'] == 'admin') {
            header("Location: adm/home_admin.php");
        } elseif ($u['tipo'] == 'veterinario') {
            header("Location: veterinario/home_veterinario.php");
        } else {
            header("Location: tutor/home_tutor.php");
        }
        exit;
    } else {
        echo "Email ou senha incorretos.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div>
        <h1>Login</h1>
        <form>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Entrar</button>
        </form>
        <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
    </div>
</body>
</html>