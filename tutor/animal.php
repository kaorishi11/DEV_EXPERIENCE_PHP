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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: animais.php");
    exit;
}

$id_tutor = $_SESSION['id'];
$id_animal = $_GET['id'];

$sql = "SELECT id, nome, especie, raca, sexo, data_nascimento, foto
        FROM animais
        WHERE id = ? AND id_tutor = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id_animal, $id_tutor);
mysqli_stmt_execute($stmt);
$resultado_animal = mysqli_stmt_get_result($stmt);
$animal = mysqli_fetch_assoc($resultado_animal);

if (!$animal) {
    header("Location: animais.php");
    exit;
}

$sql_consultas = "SELECT a.data, a.hora, a.status, s.nome AS servico,
                  u.nome AS veterinario, p.peso, p.sintomas,
                  p.diagnostico, p.observacoes, p.id AS id_prontuario
                  FROM agendamentos a
                  INNER JOIN servicos s ON a.id_servico = s.id
                  INNER JOIN veterinarios v ON a.id_veterinario = v.id
                  INNER JOIN usuarios u ON v.id_usuario = u.id
                  LEFT JOIN prontuarios p ON a.id = p.id_agendamento
                  WHERE a.id_animal = ?
                  ORDER BY a.data DESC, a.hora DESC";

$stmt = mysqli_prepare($conn, $sql_consultas);
mysqli_stmt_bind_param($stmt, "i", $id_animal);
mysqli_stmt_execute($stmt);
$resultado_consultas = mysqli_stmt_get_result($stmt);

$sql_vacinas = "SELECT vz.data_aplicacao, vz.lote, vz.data_proxima_dose,
                vac.nome AS vacina, u.nome AS veterinario
                FROM vacinacoes vz
                INNER JOIN vacinas vac ON vz.id_vacina = vac.id
                INNER JOIN veterinarios v ON vz.id_veterinario = v.id
                INNER JOIN usuarios u ON v.id_usuario = u.id
                WHERE vz.id_animal = ?
                ORDER BY vz.data_aplicacao DESC";

$stmt = mysqli_prepare($conn, $sql_vacinas);
mysqli_stmt_bind_param($stmt, "i", $id_animal);
mysqli_stmt_execute($stmt);
$resultado_vacinas = mysqli_stmt_get_result($stmt);

$sql_prescricoes = "SELECT pr.medicamento, pr.dosagem, pr.periodo_tratamento,
                    pr.instrucoes, p.id AS id_prontuario,
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
    <title><?php echo htmlspecialchars($animal['nome']); ?> - PETVIDA</title>
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
    <h1><?php echo htmlspecialchars($animal['nome']); ?></h1>

    <?php if (!empty($animal['foto'])): ?>
        <img src="../<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>" width="200">
    <?php endif; ?>

    <section>
        <h2>Dados do Animal</h2>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($animal['nome']); ?></p>
        <p><strong>Espécie:</strong> <?php echo htmlspecialchars(ucfirst($animal['especie'])); ?></p>
        <p><strong>Raça:</strong> <?php echo !empty($animal['raca']) ? htmlspecialchars($animal['raca']) : 'Não informada'; ?></p>
        <p><strong>Sexo:</strong> <?php echo $animal['sexo'] == 'macho' ? 'Macho' : 'Fêmea'; ?></p>
        <?php if (!empty($animal['data_nascimento'])): ?>
            <p><strong>Data de nascimento:</strong> <?php echo date("d/m/Y", strtotime($animal['data_nascimento'])); ?></p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Histórico de Consultas</h2>
        <?php if (mysqli_num_rows($resultado_consultas) > 0): ?>
            <?php while ($consulta = mysqli_fetch_assoc($resultado_consultas)): ?>
                <article>
                    <p><strong>Data:</strong> <?php echo date("d/m/Y", strtotime($consulta['data'])); ?></p>
                    <p><strong>Hora:</strong> <?php echo date("H:i", strtotime($consulta['hora'])); ?></p>
                    <p><strong>Serviço:</strong> <?php echo htmlspecialchars($consulta['servico']); ?></p>
                    <p><strong>Veterinário:</strong> <?php echo htmlspecialchars($consulta['veterinario']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($consulta['status']); ?></p>

                    <?php if ($consulta['peso'] !== null): ?>
                        <p><strong>Peso:</strong> <?php echo htmlspecialchars($consulta['peso']); ?> kg</p>
                    <?php endif; ?>

                    <?php if (!empty($consulta['sintomas'])): ?>
                        <p><strong>Sintomas:</strong> <?php echo nl2br(htmlspecialchars($consulta['sintomas'])); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($consulta['diagnostico'])): ?>
                        <p><strong>Diagnóstico:</strong> <?php echo nl2br(htmlspecialchars($consulta['diagnostico'])); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($consulta['observacoes'])): ?>
                        <p><strong>Observações:</strong> <?php echo nl2br(htmlspecialchars($consulta['observacoes'])); ?></p>
                    <?php endif; ?>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma consulta registrada.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Vacinas</h2>
        <?php if (mysqli_num_rows($resultado_vacinas) > 0): ?>
            <?php while ($vacina = mysqli_fetch_assoc($resultado_vacinas)): ?>
                <article>
                    <p><strong>Vacina:</strong> <?php echo htmlspecialchars($vacina['vacina']); ?></p>
                    <p><strong>Data de aplicação:</strong> <?php echo date("d/m/Y", strtotime($vacina['data_aplicacao'])); ?></p>
                    <p><strong>Lote:</strong> <?php echo htmlspecialchars($vacina['lote']); ?></p>
                    <p><strong>Veterinário:</strong> <?php echo htmlspecialchars($vacina['veterinario']); ?></p>
                    <?php if (!empty($vacina['data_proxima_dose'])): ?>
                        <p><strong>Próxima dose:</strong> <?php echo date("d/m/Y", strtotime($vacina['data_proxima_dose'])); ?></p>
                    <?php endif; ?>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma vacina registrada.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Prescrições</h2>
        <?php if (mysqli_num_rows($resultado_prescricoes) > 0): ?>
            <?php while ($prescricao = mysqli_fetch_assoc($resultado_prescricoes)): ?>
                <article>
                    <p><strong>Consulta:</strong> <?php echo date("d/m/Y", strtotime($prescricao['data'])); ?></p>
                    <p><strong>Medicamento:</strong> <?php echo htmlspecialchars($prescricao['medicamento']); ?></p>
                    <p><strong>Dosagem:</strong> <?php echo htmlspecialchars($prescricao['dosagem']); ?></p>
                    <p><strong>Período de tratamento:</strong> <?php echo htmlspecialchars($prescricao['periodo_tratamento']); ?></p>
                    <?php if (!empty($prescricao['instrucoes'])): ?>
                        <p><strong>Instruções:</strong> <?php echo nl2br(htmlspecialchars($prescricao['instrucoes'])); ?></p>
                    <?php endif; ?>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma prescrição registrada.</p>
        <?php endif; ?>
    </section>

    <a href="animais.php">Voltar para meus animais</a>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>