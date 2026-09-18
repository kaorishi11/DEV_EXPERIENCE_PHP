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
$nome_tutor = $_SESSION['nome'];

$sql_animais = "SELECT COUNT(*) AS total
                FROM animais
                WHERE id_tutor = ?";

$stmt = mysqli_prepare($conn, $sql_animais);
mysqli_stmt_bind_param($stmt, "i", $id_tutor);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$total_animais = mysqli_fetch_assoc($resultado)['total'];

$sql_consulta = "SELECT a.id, a.data, a.hora, a.status, an.nome AS animal, v.nome AS veterinario, s.nome AS servico
                 FROM agendamentos a INNER JOIN animais an ON a.id_animal = an.id
                 INNER JOIN veterinarios vet
                    ON a.id_veterinario = vet.id
                 INNER JOIN usuarios v
                    ON vet.id_usuario = v.id
                 INNER JOIN servicos s
                    ON a.id_servico = s.id
                 WHERE an.id_tutor = ?
                 AND a.data >= CURDATE()
                 AND a.status IN ('agendado', 'confirmado')
                 ORDER BY a.data ASC, a.hora ASC
                 LIMIT 1";

$stmt = mysqli_prepare($conn, $sql_consulta);
mysqli_stmt_bind_param($stmt, "i", $id_tutor);
mysqli_stmt_execute($stmt);

$resultado_consulta = mysqli_stmt_get_result($stmt);
$proxima_consulta = mysqli_fetch_assoc($resultado_consulta);

$sql_vacina = "SELECT
                  vac.nome AS vacina,
                  an.nome AS animal,
                  vz.data_proxima_dose
               FROM vacinacoes vz
               INNER JOIN animais an
                  ON vz.id_animal = an.id
               INNER JOIN vacinas vac
                  ON vz.id_vacina = vac.id
               WHERE an.id_tutor = ?
               AND vz.data_proxima_dose IS NOT NULL
               AND vz.data_proxima_dose >= CURDATE()
               ORDER BY vz.data_proxima_dose ASC
               LIMIT 1";

$stmt = mysqli_prepare($conn, $sql_vacina);
mysqli_stmt_bind_param($stmt, "i", $id_tutor);
mysqli_stmt_execute($stmt);

$resultado_vacina = mysqli_stmt_get_result($stmt);
$proxima_vacina = mysqli_fetch_assoc($resultado_vacina);

$sql_avisos = "SELECT id, titulo, mensagem, data_publicacao FROM avisos WHERE ativo = TRUE ORDER BY data_publicacao DESC LIMIT 3";
$resultado_avisos = mysqli_query($conn, $sql_avisos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PETVIDA - Área do Tutor</title>
</head>
<body>
<header>
    <h2>PETVIDA</h2>
    <nav>
        <ul>
            <li><a href="home_tutor.php">Início</a>/li>
            <li><a href="agendamentos.php">Agendamento</a></li>
            <li><a href="meus_agendamentos.php">Meus Agendamentos</a></li>
            <li><a href="animais.php">Animais</a></li>
            <li><a href="avisos.php">Avisos</a></li>
            <li><a href="prontuario.php">Prontuários</a></li>
            <li><a href="vacinas.php">Vacinas</a></li>
            <li><a href="../logout.php">Sair</a></li>
        </ul>
    </nav>
</header>
<main>
    <h1>Dashboard do Tutor</h1>
    <h2>Olá, <?php echo htmlspecialchars($nome_tutor); ?>!</h2>
    <p>Bem-vindo à área do tutor da PETVIDA.</p>
    <section>
        <h2>Resumo</h2>
        <div>
            <h3>Meus animais</h3>
            <p><?php echo $total_animais; ?></p>
            <a href="animais.php">Ver animais</a>
        </div>
        <div>
            <h3>Próxima consulta</h3>
            <?php if ($proxima_consulta): ?>
                <p><strong><?php echo htmlspecialchars($proxima_consulta['animal']); ?></strong></p>
                <p>Serviço:<?php echo htmlspecialchars($proxima_consulta['servico']); ?></p>
                <p>Data:<?php echo date("d/m/Y",strtotime($proxima_consulta['data']));?></p>
                <p>Horário:<?php echo date("H:i",strtotime($proxima_consulta['hora'])); ?></p>
                <p>Veterinário:<?php echo htmlspecialchars($proxima_consulta['veterinario']); ?></p>

                <a href="meus_agendamentos.php">Ver agendamentos</a>

            <?php else: ?>
                <p>Nenhuma consulta agendada.</p>
                <a href="agendamentos.php">Agendar consulta</a>
            <?php endif; ?>
        </div>
        <div>
            <h3>Próxima vacina</h3>
            <?php if ($proxima_vacina): ?>
                <p>Animal:<strong><?php echo htmlspecialchars($proxima_vacina['animal']); ?></strong></p>
                <p>Vacina:<?php echo htmlspecialchars($proxima_vacina['vacina']); ?></p>
                <p>Data:<?php echo date("d/m/Y",strtotime($proxima_vacina['data_proxima_dose']));?></p>
                <a href="vacinas.php">Ver vacinas</a>
            <?php else: ?>
                <p>Nenhuma vacina próxima cadastrada.</p>
            <?php endif; ?>
        </div>
            </section>
    <section>
        <h2>Avisos recentes</h2>
        <?php if (mysqli_num_rows($resultado_avisos) > 0): ?>
            <?php while ($aviso = mysqli_fetch_assoc($resultado_avisos)): ?>
                <article>
                    <h3><?php echo htmlspecialchars($aviso['titulo']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($aviso['mensagem'])); ?></p>
                    <small>Publicado em<?php echo date("d/m/Y H:i",strtotime($aviso['data_publicacao']));?></small>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum aviso disponível no momento.</p>
        <?php endif; ?>
    </section>
</main>
<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>