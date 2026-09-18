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

$sql = "SELECT
            p.id,
            p.peso,
            p.sintomas,
            p.diagnostico,
            p.observacoes,
            a.data,
            a.hora,
            an.nome AS animal,
            s.nome AS servico,
            u.nome AS veterinario,
            v.crmv
        FROM prontuarios p
        INNER JOIN agendamentos a ON p.id_agendamento = a.id
        INNER JOIN animais an ON a.id_animal = an.id
        INNER JOIN servicos s ON a.id_servico = s.id
        INNER JOIN veterinarios v ON a.id_veterinario = v.id
        INNER JOIN usuarios u ON v.id_usuario = u.id
        WHERE an.id_tutor = ?
        ORDER BY a.data DESC, a.hora DESC";

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
    <title>Prontuários - PETVIDA</title>
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
    <h1>Prontuários</h1>
    <?php if (mysqli_num_rows($resultado) > 0): ?>
        <?php while ($prontuario = mysqli_fetch_assoc($resultado)): ?>
            <article>
                <h2><?php echo htmlspecialchars($prontuario['animal']); ?></h2>
                <p>
                    <strong>Consulta:</strong>
                    <?php echo date("d/m/Y", strtotime($prontuario['data'])); ?>
                    às
                    <?php echo date("H:i", strtotime($prontuario['hora'])); ?>
                </p>
                <p>
                    <strong>Serviço:</strong>
                    <?php echo htmlspecialchars($prontuario['servico']); ?>
                </p>
                <p>
                    <strong>Veterinário:</strong>
                    <?php echo htmlspecialchars($prontuario['veterinario']); ?>
                </p>
                <p>
                    <strong>CRMV:</strong>
                    <?php echo htmlspecialchars($prontuario['crmv']); ?>
                </p>
                <?php if ($prontuario['peso'] !== null): ?>
                    <p>
                        <strong>Peso:</strong>
                        <?php echo htmlspecialchars($prontuario['peso']); ?> kg
                    </p>
                <?php endif; ?>
                <?php if (!empty($prontuario['sintomas'])): ?>
                    <p>
                        <strong>Sintomas:</strong><br>
                        <?php echo nl2br(htmlspecialchars($prontuario['sintomas'])); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($prontuario['diagnostico'])): ?>
                    <p>
                        <strong>Diagnóstico:</strong><br>
                        <?php echo nl2br(htmlspecialchars($prontuario['diagnostico'])); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($prontuario['observacoes'])): ?>
                    <p>
                        <strong>Observações:</strong><br>
                        <?php echo nl2br(htmlspecialchars($prontuario['observacoes'])); ?>
                    </p>
                <?php endif; ?>

                <?php
                $id_prontuario = $prontuario['id'];

                $sql_prescricoes = "SELECT medicamento, dosagem, periodo_tratamento, instrucoes
                                    FROM prescricoes
                                    WHERE id_prontuario = ?";

                $stmt_prescricoes = mysqli_prepare($conn, $sql_prescricoes);
                mysqli_stmt_bind_param($stmt_prescricoes, "i", $id_prontuario);
                mysqli_stmt_execute($stmt_prescricoes);
                $resultado_prescricoes = mysqli_stmt_get_result($stmt_prescricoes);
                ?>

                <?php if (mysqli_num_rows($resultado_prescricoes) > 0): ?>

                    <h3>Prescrições</h3>

                    <?php while ($prescricao = mysqli_fetch_assoc($resultado_prescricoes)): ?>

                        <div>
                            <p>
                                <strong>Medicamento:</strong>
                                <?php echo htmlspecialchars($prescricao['medicamento']); ?>
                            </p>

                            <p>
                                <strong>Dosagem:</strong>
                                <?php echo htmlspecialchars($prescricao['dosagem']); ?>
                            </p>

                            <p>
                                <strong>Período de tratamento:</strong>
                                <?php echo htmlspecialchars($prescricao['periodo_tratamento']); ?>
                            </p>

                            <?php if (!empty($prescricao['instrucoes'])): ?>
                                <p>
                                    <strong>Instruções:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($prescricao['instrucoes'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <p>Nenhuma prescrição registrada.</p>

                <?php endif; ?>

            </article>

            <hr>

        <?php endwhile; ?>

    <?php else: ?>

        <p>Nenhum prontuário encontrado.</p>

    <?php endif; ?>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>

</body>
</html>