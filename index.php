<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PETVIDA - Clínica Veterinária Amigo Fiel</title>
</head>
<body>
    <div>
        <h1>PETVIDA</h1>
        <h2>Clínica Veterinária Amigo Fiel</h2>
        <p>Cuidando da saúde e do bem-estar do seu melhor amigo.</p>

        <?php if (isset($_SESSION['id'])): ?>
            <?php if ($_SESSION['tipo_usuario'] == 'administrador'): ?>
                <a href="adm/home_admin.php"><button>Acessar sistema</button></a>

            <?php elseif ($_SESSION['tipo_usuario'] == 'veterinario'): ?>
                <a href="veterinario/home_veterinario.php"><button>Acessar sistema</button></a>

            <?php else: ?>
                <a href="tutor/home_tutor.php"><button>Acessar sistema</button></a>
            <?php endif; ?>

        <?php else: ?>
            <a href="login.php"><button>Entrar</button></a>
            <a href="cadastro.php"><button>Cadastrar</button></a>
        <?php endif; ?>
    </div>
    <footer>
        <div>
            <p>© 2026 PETVIDA — Clínica Veterinária Amigo Fiel</p>
        </div>
    </footer>
</body>
</html>