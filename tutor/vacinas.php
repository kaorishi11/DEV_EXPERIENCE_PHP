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

$sql_vacinas = "SELECT vz.id, vz.data_aplicacao, vz.lote, vz.data_proxima_dose,
                an.nome AS animal, vac.nome AS vacina,
                vac.descricao AS descricao_vacina,
                u.nome AS veterinario, vet.crmv
                FROM vacinacoes vz
                INNER JOIN animais an ON vz.id_animal = an.id
                INNER JOIN vacinas vac ON vz.id_vacina = vac.id
                INNER JOIN veterinarios vet ON vz.id_veterinario = vet.id
                INNER JOIN usuarios u ON vet.id_usuario = u.id
                WHERE an.id_tutor = ?
                ORDER BY vz.data_aplicacao DESC";

$stmt = mysqli_prepare($conn, $sql_vacinas);
mysqli_stmt_bind_param($stmt, "i", $id_tutor);
mysqli_stmt_execute($stmt);
$resultado_vacinas = mysqli_stmt_get_result($stmt);

$sql_proximas = "SELECT vz.id, vz.data_proxima_dose,
                 an.nome AS animal, vac.nome AS vacina
                 FROM vacinacoes vz
                 INNER JOIN animais an ON vz.id_animal = an.id
                 INNER JOIN vacinas vac ON vz.id_vacina = vac.id
                 WHERE an.id_tutor = ?
                 AND vz.data_proxima_dose IS NOT NULL
                 AND vz.data_proxima_dose >= CURDATE()
                 ORDER BY vz.data_proxima_dose ASC";

$stmt = mysqli_prepare($conn, $sql_proximas);
mysqli_stmt_bind_param($stmt, "i", $id_tutor);
mysqli_stmt_execute($stmt);
$resultado_proximas = mysqli_stmt_get_result($stmt);

$sql_atrasadas = "SELECT vz.id, vz.data_proxima_dose,
                  an.nome AS animal, vac.nome AS vacina
                  FROM vacinacoes vz
                  INNER JOIN animais an ON vz.id_animal = an.id
                  INNER JOIN vacinas vac ON vz.id_vacina = vac.id
                  WHERE an.id_tutor = ?
                  AND vz.data_proxima_dose IS NOT NULL
                  AND vz.data_proxima_dose < CURDATE()
                  ORDER BY vz.data_proxima_dose ASC";

$stmt = mysqli_prepare($conn, $sql_atrasadas);
mysqli_stmt_bind_param($stmt, "i", $id_tutor);
mysqli_stmt_execute($stmt);
$resultado_atrasadas = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacinas - PETVIDA</title>
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
    <h1>Vacinas dos meus animais</h1>

    <section>
        <h2>Vacinas Atrasadas</h2>

        <?php if (mysqli_num_rows($resultado_atrasadas) > 0): ?>
            <?php while ($vacina = mysqli_fetch_assoc($resultado_atrasadas)): ?>
                <article>
                    <h3><?php echo htmlspecialchars($vacina['animal']); ?></h3>
                    <p><strong>Vacina:</strong> <?php echo htmlspecialchars($vacina['vacina']); ?></p>
                    <p><strong>Data prevista:</strong> <?php echo date("d/m/Y", strtotime($vacina['data_proxima_dose'])); ?></p>
                    <p>Esta vacina está atrasada.</p>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma vacina atrasada.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Próximas Doses</h2>

        <?php if (mysqli_num_rows($resultado_proximas) > 0): ?>
            <?php while ($vacina = mysqli_fetch_assoc($resultado_proximas)): ?>
                <article>
                    <h3><?php echo htmlspecialchars($vacina['animal']); ?></h3>
                    <p><strong>Vacina:</strong> <?php echo htmlspecialchars($vacina['vacina']); ?></p>
                    <p><strong>Próxima dose:</strong> <?php echo date("d/m/Y", strtotime($vacina['data_proxima_dose'])); ?></p>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma próxima dose cadastrada.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Vacinas Aplicadas</h2>

        <?php if (mysqli_num_rows($resultado_vacinas) > 0): ?>
            <?php while ($vacina = mysqli_fetch_assoc($resultado_vacinas)): ?>
                <article>
                    <h3><?php echo htmlspecialchars($vacina['animal']); ?></h3>
                    <p><strong>Vacina:</strong> <?php echo htmlspecialchars($vacina['vacina']); ?></p>

                    <?php if (!empty($vacina['descricao_vacina'])): ?>
                        <p><strong>Descrição:</strong> <?php echo htmlspecialchars($vacina['descricao_vacina']); ?></p>
                    <?php endif; ?>

                    <p><strong>Data da aplicação:</strong> <?php echo date("d/m/Y", strtotime($vacina['data_aplicacao'])); ?></p>
                    <p><strong>Lote:</strong> <?php echo htmlspecialchars($vacina['lote']); ?></p>
                    <p><strong>Veterinário:</strong> <?php echo htmlspecialchars($vacina['veterinario']); ?></p>
                    <p><strong>CRMV:</strong> <?php echo htmlspecialchars($vacina['crmv']); ?></p>

                    <?php if (!empty($vacina['data_proxima_dose'])): ?>
                        <p><strong>Próxima dose:</strong> <?php echo date("d/m/Y", strtotime($vacina['data_proxima_dose'])); ?></p>
                    <?php else: ?>
                        <p>Não há próxima dose cadastrada.</p>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhuma vacinação registrada para seus animais.</p>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>

</body>
</html>