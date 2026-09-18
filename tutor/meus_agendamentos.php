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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['cancelar_id'])) {
        $id_agendamento = intval($_POST['cancelar_id']);
        $sql_cancelar = "UPDATE agendamentos a
                         INNER JOIN animais an
                            ON a.id_animal = an.id
                         SET a.status = 'cancelado'
                         WHERE a.id = ?
                         AND an.id_tutor = ?
                         AND a.status IN ('agendado', 'confirmado')
                         AND a.data >= CURDATE()";
        $stmt = mysqli_prepare($conn, $sql_cancelar);
        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $id_agendamento,
            $id_tutor
        );
        mysqli_stmt_execute($stmt);
    }
}

$sql_proximos = "SELECT
                    a.id,
                    a.data,
                    a.hora,
                    a.status,
                    a.observacoes,
                    an.nome AS animal,
                    v.nome AS veterinario,
                    s.nome AS servico,
                    s.valor
                 FROM agendamentos a
                 INNER JOIN animais an
                    ON a.id_animal = an.id
                 INNER JOIN veterinarios vet
                    ON a.id_veterinario = vet.id
                 INNER JOIN usuarios v
                    ON vet.id_usuario = v.id
                 INNER JOIN servicos s
                    ON a.id_servico = s.id
                 WHERE an.id_tutor = ?
                 AND (
                    a.data > CURDATE()
                    OR (
                        a.data = CURDATE()
                        AND a.hora >= CURTIME()
                    )
                 )
                 AND a.status IN ('agendado', 'confirmado')
                 ORDER BY a.data ASC, a.hora ASC";
$stmt = mysqli_prepare($conn, $sql_proximos);
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_tutor
);
mysqli_stmt_execute($stmt);
$resultado_proximos = mysqli_stmt_get_result($stmt);

$sql_anteriores = "SELECT
                      a.id,
                      a.data,
                      a.hora,
                      a.status,
                      an.nome AS animal,
                      v.nome AS veterinario,
                      s.nome AS servico
                   FROM agendamentos a
                   INNER JOIN animais an
                      ON a.id_animal = an.id
                   INNER JOIN veterinarios vet
                      ON a.id_veterinario = vet.id
                   INNER JOIN usuarios v
                      ON vet.id_usuario = v.id
                   INNER JOIN servicos s
                      ON a.id_servico = s.id
                   WHERE an.id_tutor = ?
                   AND (
                       a.data < CURDATE()
                       OR a.status IN ('realizado', 'cancelado')
                   )
                   ORDER BY a.data DESC, a.hora DESC";
$stmt = mysqli_prepare($conn, $sql_anteriores);
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_tutor
);
mysqli_stmt_execute($stmt);
$resultado_anteriores = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - PETVIDA</title>
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
    <h1>Meus Agendamentos</h1>
    <section>
        <h2>Próximos Agendamentos</h2>
        <?php if (mysqli_num_rows($resultado_proximos) > 0): ?>
            <?php while ($agendamento = mysqli_fetch_assoc($resultado_proximos)): ?>
                <article>
                    <h3><?php echo htmlspecialchars($agendamento['animal']); ?></h3>
                    <p><strong>Serviço:</strong><?php echo htmlspecialchars($agendamento['servico']); ?></p>
                    <p><strong>Veterinário:</strong><?php echo htmlspecialchars($agendamento['veterinario']); ?></p>
                    <p><strong>Data:</strong><?php echo date("d/m/Y",strtotime($agendamento['data']));?></p>
                    <p><strong>Horário:</strong><?php echo date("H:i",strtotime($agendamento['hora']));?></p>
                    <p>
                        <strong>Status:</strong>
                        <?php
                        if ($agendamento['status'] == 'agendado') {
                            echo "Agendado";
                        } elseif ($agendamento['status'] == 'confirmado') {
                            echo "Confirmado";
                        }
                        ?>
                    </p>
                    <?php if (!empty($agendamento['observacoes'])): ?>
                        <p>
                            <strong>Observações:</strong>
                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $agendamento['observacoes']
                                )
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="cancelar_id" value="<?php echo $agendamento['id'];?>">
                        <button type="submit">Cancelar agendamento</button>
                    </form>
                </article>
                <hr>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Você não possui próximos agendamentos.</p>
            <a href="agendamentos.php">Fazer um novo agendamento</a>
        <?php endif; ?>

    </section>
    <section>
        <h2>Atendimentos Anteriores</h2>
        <?php if (mysqli_num_rows($resultado_anteriores) > 0): ?>
            <?php while ($agendamento = mysqli_fetch_assoc($resultado_anteriores)): ?>
                <article>
                    <h3><?php echo htmlspecialchars($agendamento['animal']); ?></h3>
                    <p><strong>Serviço:</strong><?php echo htmlspecialchars($agendamento['servico']); ?></p>
                    <p><strong>Veterinário:</strong><?php echo htmlspecialchars($agendamento['veterinario']); ?></p>
                    <p><strong>Data:</strong><?php echo date("d/m/Y", strtotime($agendamento['data']));?></p>
                    <p><strong>Horário:</strong><?php echo date("H:i",strtotime($agendamento['hora']));?></p>
                    <p>
                        <strong>Status:</strong>
                        <?php
                        switch ($agendamento['status']) {
                            case 'realizado':
                                echo "Realizado";
                                break;
                            case 'cancelado':
                                echo "Cancelado";
                                break;
                            case 'agendado':
                                echo "Agendado";
                                break;
                            case 'confirmado':
                                echo "Confirmado";
                                break;
                        }
                        ?>
                    </p>
                </article>
                <hr>
            <?php endwhile; ?>

        <?php else: ?>
            <p>Nenhum atendimento anterior encontrado.</p>
        <?php endif; ?>
    </section>
</main>
<footer>
    <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
</footer>
</body>
</html>