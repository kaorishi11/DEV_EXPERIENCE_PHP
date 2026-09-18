<?php
session_start();
include "../conexao.php";

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] != 'administrador') {
    header("Location: ../login.php");
    exit;
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? "";
    $id = intval($_POST['id'] ?? 0);

    if ($acao == "cadastrar") {
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $quantidade = intval($_POST['quantidade_estoque']);
        $minimo = intval($_POST['estoque_minimo']);

        if ($nome == "" || $quantidade < 0 || $minimo < 0) {
            $mensagem = "Preencha os campos corretamente.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO vacinas (nome,descricao,quantidade_estoque,estoque_minimo,ativo) VALUES (?,?,?,?,TRUE)");
            mysqli_stmt_bind_param($stmt, "ssii", $nome, $descricao, $quantidade, $minimo);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: estoque.php?sucesso=cadastrado");
                exit;
            }

            $mensagem = "Não foi possível cadastrar a vacina.";
        }
    }

    if ($acao == "editar") {
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $quantidade = intval($_POST['quantidade_estoque']);
        $minimo = intval($_POST['estoque_minimo']);

        if ($nome == "" || $quantidade < 0 || $minimo < 0) {
            $mensagem = "Preencha os campos corretamente.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE vacinas SET nome=?,descricao=?,quantidade_estoque=?,estoque_minimo=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssiii", $nome, $descricao, $quantidade, $minimo, $id);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: estoque.php?sucesso=editado");
                exit;
            }

            $mensagem = "Não foi possível editar a vacina.";
        }
    }

    if ($acao == "movimentacao") {
        $tipo = $_POST['tipo'];
        $quantidade = intval($_POST['quantidade']);

        if ($quantidade <= 0) {
            $mensagem = "Informe uma quantidade válida.";
        } elseif ($tipo == "entrada") {
            $stmt = mysqli_prepare($conn, "UPDATE vacinas SET quantidade_estoque=quantidade_estoque+? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ii", $quantidade, $id);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: estoque.php?sucesso=entrada");
                exit;
            }

            $mensagem = "Não foi possível registrar a entrada.";
        } elseif ($tipo == "saida") {
            $stmt = mysqli_prepare($conn, "UPDATE vacinas SET quantidade_estoque=quantidade_estoque-? WHERE id=? AND quantidade_estoque>=?");
            mysqli_stmt_bind_param($stmt, "iii", $quantidade, $id, $quantidade);

            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                header("Location: estoque.php?sucesso=saida");
                exit;
            }

            $mensagem = "Quantidade insuficiente em estoque.";
        }
    }
}

$editar = null;

if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = mysqli_prepare($conn, "SELECT id,nome,descricao,quantidade_estoque,estoque_minimo FROM vacinas WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado_editar = mysqli_stmt_get_result($stmt);
    $editar = mysqli_fetch_assoc($resultado_editar);
}

if (isset($_GET['alternar']) && is_numeric($_GET['alternar'])) {
    $id = intval($_GET['alternar']);
    $stmt = mysqli_prepare($conn, "UPDATE vacinas SET ativo=NOT ativo WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: estoque.php?sucesso=alterado");
        exit;
    }
}

$resultado = mysqli_query($conn, "SELECT id,nome,descricao,quantidade_estoque,estoque_minimo,ativo FROM vacinas ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Administrador</title>
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

    <h1>Gerenciar Estoque</h1>

    <?php if ($mensagem): ?>
        <p><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <p>Operação realizada com sucesso.</p>
    <?php endif; ?>

    <h2><?php echo $editar ? "Editar Vacina" : "Cadastrar Vacina"; ?></h2>

    <form method="POST">
        <input type="hidden" name="acao" value="<?php echo $editar ? 'editar' : 'cadastrar'; ?>">

        <?php if ($editar): ?>
            <input type="hidden" name="id" value="<?php echo $editar['id']; ?>">
        <?php endif; ?>

        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($editar['nome'] ?? ''); ?>" required>

        <label>Descrição:</label>
        <textarea name="descricao"><?php echo htmlspecialchars($editar['descricao'] ?? ''); ?></textarea>

        <label>Quantidade:</label>
        <input type="number" name="quantidade_estoque" min="0" value="<?php echo htmlspecialchars($editar['quantidade_estoque'] ?? '0'); ?>" required>

        <label>Estoque mínimo:</label>
        <input type="number" name="estoque_minimo" min="0" value="<?php echo htmlspecialchars($editar['estoque_minimo'] ?? '5'); ?>" required>

        <button type="submit"><?php echo $editar ? "Salvar alterações" : "Cadastrar vacina"; ?></button>

        <?php if ($editar): ?>
            <a href="estoque.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Movimentar estoque</h2>

    <form method="POST">
        <input type="hidden" name="acao" value="movimentacao">

        <label>Vacina:</label>
        <select name="id" required>
            <option value="">Selecione</option>
            <?php
            mysqli_data_seek($resultado, 0);
            while ($vacina = mysqli_fetch_assoc($resultado)):
            ?>
                <option value="<?php echo $vacina['id']; ?>"><?php echo htmlspecialchars($vacina['nome']); ?></option>
            <?php endwhile; ?>
        </select>

        <label>Tipo:</label>
        <select name="tipo" required>
            <option value="entrada">Entrada</option>
            <option value="saida">Saída</option>
        </select>

        <label>Quantidade:</label>
        <input type="number" name="quantidade" min="1" required>

        <button type="submit">Registrar movimentação</button>
    </form>

    <h2>Estoque de Vacinas</h2>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Vacina</th>
            <th>Quantidade</th>
            <th>Estoque mínimo</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>

        <?php
        mysqli_data_seek($resultado, 0);
        while ($vacina = mysqli_fetch_assoc($resultado)):
        ?>
            <tr>
                <td><?php echo $vacina['id']; ?></td>
                <td><?php echo htmlspecialchars($vacina['nome']); ?></td>
                <td><?php echo $vacina['quantidade_estoque']; ?></td>
                <td><?php echo $vacina['estoque_minimo']; ?></td>
                <td>
                    <?php
                    if (!$vacina['ativo']) {
                        echo "Inativo";
                    } elseif ($vacina['quantidade_estoque'] <= $vacina['estoque_minimo']) {
                        echo "Estoque baixo";
                    } else {
                        echo "Normal";
                    }
                    ?>
                </td>
                <td>
                    <a href="estoque.php?editar=<?php echo $vacina['id']; ?>">Editar</a>
                    <a href="estoque.php?alternar=<?php echo $vacina['id']; ?>"><?php echo $vacina['ativo'] ? 'Desativar' : 'Ativar'; ?></a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>