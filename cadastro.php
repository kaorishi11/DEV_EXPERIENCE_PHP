<?php
include 'conexao.php';
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $tipo_usuario = $_POST['tipo_usuario'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($senha !== $confirmar_senha) {
        $mensagem = "As senhas não coincidem.";
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultado) > 0) {
            $mensagem = "Este e-mail já está cadastrado.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios 
                    (nome, email, senha, telefone, tipo_usuario)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $nome,
                $email,
                $senha_hash,
                $telefone,
                $tipo_usuario
            );
            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php?cadastro=sucesso");
                exit;
            } else {
                $mensagem = "Erro ao realizar o cadastro: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - PETVIDA</title>
</head>
<body>
    <div>
        <h1>Cadastro</h1>
        <?php if ($mensagem != ""): ?>
            <p><?php echo htmlspecialchars($mensagem); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>

            <br><br>
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <br><br>
            <label for="telefone">Telefone:</label>
            <input type="tel" id="telefone" name="telefone" required>

            <br><br>
            <label for="tipo_usuario">Tipo de usuário:</label>
            <select id="tipo_usuario" name="tipo_usuario" required>
                <option value="">Selecione</option>
                <option value="tutor">Tutor</option>
                <option value="veterinario">Veterinário</option>
            </select>

            <br><br>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <br><br>
            <label for="confirmar_senha">Confirmar senha:</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required>

            <br><br>
            <button type="submit">Cadastrar</button>
        </form>
        <p>Já possui uma conta?<a href="login.php">Faça login</a></p>
    </div>
</body>
</html>