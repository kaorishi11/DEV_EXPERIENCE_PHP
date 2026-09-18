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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tutor = $_SESSION['id'];
    $nome = trim($_POST['nome']);
    $especie = $_POST['especie'];
    $raca = trim($_POST['raca']);
    $sexo = $_POST['sexo'];
    $data_nascimento = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;
    $foto = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $extensoes_permitidas)) {
            $nome_foto = uniqid() . "." . $extensao;
            $pasta = "../img/animais/";

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $pasta . $nome_foto)) {
                $foto = "img/animais/" . $nome_foto;
            }
        }
    }

    $sql = "INSERT INTO animais (id_tutor, nome, especie, raca, sexo, data_nascimento, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issssss", $id_tutor, $nome, $especie, $raca, $sexo, $data_nascimento, $foto);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: animais.php?cadastro=sucesso");
        exit;
    }

    $erro = "Não foi possível cadastrar o animal.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Animal - PETVIDA</title>
</head>
<body>
<header>
    <h2>PETVIDA</h2>
    <nav>
        <a href="home_tutor.php">Início</a>
        <a href="agendamentos.php">Agendamento</a>
        <a href="meus_agendamentos.php">Meus Agendamentos</a>
        <a href="animais.php">Animais</a>
        <a href="avisos.php">Avisos</a>
        <a href="prontuario.php">Prontuários</a>
        <a href="vacinas.php">Vacinas</a>
        <a href="../logout.php">Sair</a>
    </nav>
</header>

<main>
    <h1>Cadastro de Animal</h1>

    <?php if (isset($erro)): ?>
        <p><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="especie">Espécie:</label>
        <select id="especie" name="especie" required>
            <option value="">Selecione</option>
            <option value="cachorro">Cachorro</option>
            <option value="gato">Gato</option>
        </select>

        <label for="raca">Raça:</label>
        <input type="text" id="raca" name="raca">

        <label for="sexo">Sexo:</label>
        <select id="sexo" name="sexo" required>
            <option value="">Selecione</option>
            <option value="macho">Macho</option>
            <option value="femea">Fêmea</option>
        </select>

        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento">

        <label for="foto">Foto:</label>
        <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp">

        <button type="submit">Cadastrar</button>
    </form>

    <a href="animais.php">Voltar</a>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>