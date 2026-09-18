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

$id_tutor = $_SESSION['id'];

$sql = "SELECT id, nome, especie, raca, sexo, data_nascimento, foto
        FROM animais
        WHERE id_tutor = ?
        ORDER BY nome ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_tutor);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Animais - PETVIDA</title>
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
    <h1>Meus Animais</h1>
    <a href="cadastrar_animal.php">Adicionar Animal</a>

    <?php if (mysqli_num_rows($resultado) > 0): ?>
        <?php while ($animal = mysqli_fetch_assoc($resultado)): ?>
            <article>
                <?php if (!empty($animal['foto'])): ?>
                    <img src="../<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>" width="150">
                <?php endif; ?>

                <h2><?php echo htmlspecialchars($animal['nome']); ?></h2>
                <p><strong>Espécie:</strong> <?php echo htmlspecialchars(ucfirst($animal['especie'])); ?></p>
                <p><strong>Raça:</strong> <?php echo !empty($animal['raca']) ? htmlspecialchars($animal['raca']) : 'Não informada'; ?></p>
                <p><strong>Sexo:</strong> <?php echo $animal['sexo'] == 'macho' ? 'Macho' : 'Fêmea'; ?></p>

                <?php if (!empty($animal['data_nascimento'])): ?>
                    <p><strong>Data de nascimento:</strong> <?php echo date("d/m/Y", strtotime($animal['data_nascimento'])); ?></p>
                <?php endif; ?>
            </article>
            <hr>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Você ainda não possui animais cadastrados.</p>
    <?php endif; ?>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>