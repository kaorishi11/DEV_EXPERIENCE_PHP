<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['tipo_usuario'] != 'veterinario') {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: animais.php");
    exit;
}

$id_animal = $_GET['id'];

$sql_animal = "SELECT an.id, an.nome, an.especie, an.raca, an.sexo,
               an.data_nascimento, u.nome AS tutor
               FROM animais an
               INNER JOIN usuarios u ON an.id_tutor = u.id
               WHERE an.id = ?";

$stmt = mysqli_prepare($conn, $sql_animal);
mysqli_stmt_bind_param($stmt, "i", $id_animal);
mysqli_stmt_execute($stmt);
$resultado_animal = mysqli_stmt_get_result($stmt);
$animal = mysqli_fetch_assoc($resultado_animal);

if (!$animal) {
    header("Location: animais.php");
    exit;
}

$sql_historico = "SELECT p.id, p.peso, p.sintomas, p.diagnostico,
                  p.observacoes, a.data, a.hora, a.status,
                  s.nome AS servico, u.nome AS veterinario
                  FROM prontuarios p
                  INNER JOIN agendamentos a ON p.id_agendamento = a.id
                  INNER JOIN servicos s ON a.id_servico = s.id
                  INNER JOIN veterinarios v ON a.id_veterinario = v.id
                  INNER JOIN usuarios u ON v.id_usuario = u.id
                  WHERE a.id_animal = ?
                  ORDER BY a.data DESC, a.hora DESC";

$stmt = mysqli_prepare($conn, $sql_historico);
mysqli_stmt_bind_param($stmt, "i", $id_animal);
mysqli_stmt_execute($stmt);
$resultado_historico = mysqli_stmt_get_result($stmt);

$sql_prescricoes = "SELECT pr.medicamento, pr.dosagem,
                    pr.periodo_tratamento, pr.instrucoes,
                    a.data
                    FROM prescricoes pr
                    INNER JOIN prontuarios p ON pr.id_prontuario = p.id
                    INNER JOIN agendamentos a ON p.id_agendamento = a.id
                    WHERE a.id_animal = ?
                    ORDER BY a.data DESC";

$stmt = mysqli_prepare($conn, $sql_prescricoes);
mysqli_stmt_bind_param($stmt, "i", $id_animal);
mysqli_stmt_execute($stmt);
$resultado_prescricoes = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico do Animal - PETVIDA</title>
</head>
<body>
<header>
    <h2>PETVIDA</h2>
    <nav>
        <a href="home_veterinario.php">Início</a>
        <a href="agenda_veterinario.php">Agenda</a>
        <a href="vacina.php">Vacina</a>
        <a href="atendimento.php">Atendimentos</a>
        <a href="historico_animal.php">Animais</a>
        <a href="prescricao.php">Prescrição</a>
        <a href="perfil_veterinario.php">Perfil</a>
        <a href="../logout.php">Sair</a>
    </nav>
</header>

<main>
    <h1>Histórico do Animal</h1>

    <section>
        <h2>Dados do Animal</h2>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($animal['nome']); ?></p>
        <p><strong>Espécie:</strong> <?php echo htmlspecialchars(ucfirst($animal['especie'])); ?></p>
        <p><strong>Raça:</strong> <?php echo !empty($animal['raca']) ? htmlspecialchars($animal['raca']) : 'Não informada'; ?></p>
        <p><strong>Sexo:</strong> <?php echo $animal['sexo'] == 'macho' ? 'Macho' : 'Fêmea'; ?></p>
        <p><strong>Tutor:</strong> <?php echo htmlspecialchars($animal['tutor']); ?></p>
        <?php if (!empty($animal['data_nascimento'])): ?>
            <p><strong>Data de nascimento:</strong> <?php echo date("d/m/Y", strtotime($animal['data_nascimento'])); ?></p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Consultas Anteriores</h2>

        <?php if (mysqli_num_rows($resultado_historico) > 0): ?>
            <?php while ($consulta = mysqli_fetch_assoc($resultado_historico)): ?>
                <article>
                    <h3><?php echo htmlspecialchars($consulta['servico']); ?></h3>
                    <p><strong>Data:</strong> <?php echo date("d/m/Y", strtotime($consulta['data'])); ?></p>
                    <p><strong>Horário:</strong> <?php echo date("H:i", strtotime($consulta['hora'])); ?></p>
                    <p><strong>Veterinário:</strong> <?php echo htmlspecialchars($consulta['veterinario']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($consulta['status']); ?></p>

                    <?php if ($consulta['peso'] !== null): ?>
                        <p><strong>Peso:</strong> <?php echo htmlspecialchars($consulta['peso']); ?> kg</p>
                    <?php endif; ?>

                    <?php if (!empty($consulta['sintomas'])): ?>
                        <p><strong>Sintomas:</strong><br><?php echo nl2br(htmlspecialchars($consulta['sintomas'])); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($consulta['diagnostico'])): ?>
                        <p><strong>Diagnóstico:</strong><br><?php echo nl2br(htmlspecialchars($consulta['diagnostico'])); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($consulta['observacoes'])): ?>
                        <p><strong>Exames/Observações:</strong><br><?php echo nl2br(htmlspecialchars($consulta['observacoes'])); ?></p>
                    <?php endif; ?>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma consulta anterior registrada.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Prescrições</h2>

        <?php if (mysqli_num_rows($resultado_prescricoes) > 0): ?>
            <?php while ($prescricao = mysqli_fetch_assoc($resultado_prescricoes)): ?>
                <article>
                    <p><strong>Data da consulta:</strong> <?php echo date("d/m/Y", strtotime($prescricao['data'])); ?></p>
                    <p><strong>Medicamento:</strong> <?php echo htmlspecialchars($prescricao['medicamento']); ?></p>
                    <p><strong>Dosagem:</strong> <?php echo htmlspecialchars($prescricao['dosagem']); ?></p>
                    <p><strong>Período de tratamento:</strong> <?php echo htmlspecialchars($prescricao['periodo_tratamento']); ?></p>

                    <?php if (!empty($prescricao['instrucoes'])): ?>
                        <p><strong>Instruções:</strong><br><?php echo nl2br(htmlspecialchars($prescricao['instrucoes'])); ?></p>
                    <?php endif; ?>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma prescrição registrada.</p>
        <?php endif; ?>
    </section>

    <a href="animais.php">Voltar para animais</a>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>