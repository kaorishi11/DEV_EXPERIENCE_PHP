<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: ../login.php");
    exit;
}
include "../conexao.php";

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM agendamentos WHERE data BETWEEN ? AND ?");
mysqli_stmt_bind_param($stmt, "ss", $data_inicio, $data_fim);
mysqli_stmt_execute($stmt);
$atendimentos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

$stmt = mysqli_prepare($conn, "SELECT s.nome, COUNT(a.id) AS total FROM agendamentos a JOIN servicos s ON a.id_servico = s.id WHERE a.data BETWEEN ? AND ? GROUP BY s.id, s.nome ORDER BY total DESC");
mysqli_stmt_bind_param($stmt, "ss", $data_inicio, $data_fim);
mysqli_stmt_execute($stmt);
$servicos = mysqli_stmt_get_result($stmt);

$stmt = mysqli_prepare($conn, "SELECT u.nome, COUNT(a.id) AS total FROM agendamentos a JOIN veterinarios v ON a.id_veterinario = v.id JOIN usuarios u ON v.id_usuario = u.id WHERE a.data BETWEEN ? AND ? GROUP BY v.id, u.nome ORDER BY total DESC");
mysqli_stmt_bind_param($stmt, "ss", $data_inicio, $data_fim);
mysqli_stmt_execute($stmt);
$veterinarios = mysqli_stmt_get_result($stmt);

$vacinas_atrasadas = mysqli_query($conn, "SELECT a.nome AS animal, va.nome AS vacina, vz.data_proxima_dose FROM vacinacoes vz JOIN animais a ON vz.id_animal = a.id JOIN vacinas va ON vz.id_vacina = va.id WHERE vz.data_proxima_dose IS NOT NULL AND vz.data_proxima_dose < CURDATE() ORDER BY vz.data_proxima_dose");

$estoque = mysqli_query($conn, "SELECT nome, quantidade_estoque, estoque_minimo, ativo FROM vacinas ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - PETVIDA</title>
</head>
<body>
    <div>
        <ul>
            <li><a href="home_admin.php">Início</a></li>
            <li><a href="usuarios.php">Usuários</a></li>
            <li><a href="veterinarios.php">Veterinários</a></li>
            <li><a href="horarios.php">Horários</a></li>
            <li><a href="servicos.php">Serviços</a></li>
            <li><a href="avisos_admin.php">Avisos</a></li>
            <li><a href="estoque.php">Estoque</a></li>
            <li><a href="relatorios.php">Relatórios</a></li>
            <li><a href="../logout.php">Sair</a></li>
        </ul>
    </div>

    <h1>Relatórios</h1>

    <form method="GET">
        <label>Data inicial:</label>
        <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" required>

        <label>Data final:</label>
        <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" required>

        <button type="submit">Filtrar</button>
    </form>

    <h2>Atendimentos por período</h2>
    <p>Total de agendamentos: <strong><?= $atendimentos ?></strong></p>

    <h2>Serviços mais procurados</h2>
    <?php if (mysqli_num_rows($servicos) > 0): ?>
        <table border="1">
            <tr>
                <th>Serviço</th>
                <th>Quantidade</th>
            </tr>
            <?php while ($servico = mysqli_fetch_assoc($servicos)): ?>
                <tr>
                    <td><?= htmlspecialchars($servico['nome']) ?></td>
                    <td><?= $servico['total'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Nenhum atendimento encontrado no período.</p>
    <?php endif; ?>

    <h2>Atendimentos por veterinário</h2>
    <?php if (mysqli_num_rows($veterinarios) > 0): ?>
        <table border="1">
            <tr>
                <th>Veterinário</th>
                <th>Quantidade</th>
            </tr>
            <?php while ($veterinario = mysqli_fetch_assoc($veterinarios)): ?>
                <tr>
                    <td><?= htmlspecialchars($veterinario['nome']) ?></td>
                    <td><?= $veterinario['total'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Nenhum atendimento encontrado no período.</p>
    <?php endif; ?>

    <h2>Vacinas em atraso</h2>
    <?php if (mysqli_num_rows($vacinas_atrasadas) > 0): ?>
        <table border="1">
            <tr>
                <th>Animal</th>
                <th>Vacina</th>
                <th>Próxima dose</th>
            </tr>
            <?php while ($vacina = mysqli_fetch_assoc($vacinas_atrasadas)): ?>
                <tr>
                    <td><?= htmlspecialchars($vacina['animal']) ?></td>
                    <td><?= htmlspecialchars($vacina['vacina']) ?></td>
                    <td><?= date('d/m/Y', strtotime($vacina['data_proxima_dose'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Nenhuma vacina em atraso.</p>
    <?php endif; ?>

    <h2>Estoque</h2>
    <table border="1">
        <tr>
            <th>Vacina</th>
            <th>Quantidade</th>
            <th>Estoque mínimo</th>
            <th>Status</th>
        </tr>
        <?php while ($item = mysqli_fetch_assoc($estoque)): ?>
            <tr>
                <td><?= htmlspecialchars($item['nome']) ?></td>
                <td><?= $item['quantidade_estoque'] ?></td>
                <td><?= $item['estoque_minimo'] ?></td>
                <td>
                    <?php
                    if (!$item['ativo']) {
                        echo "Inativo";
                    } elseif ($item['quantidade_estoque'] <= $item['estoque_minimo']) {
                        echo "Estoque baixo";
                    } else {
                        echo "Normal";
                    }
                    ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>