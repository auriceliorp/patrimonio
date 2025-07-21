<?php
//
// Sistema: Bens patrimoniais
// Modulo: Login e Autenticação
// Autor: Adil D. Pinto Jr.
// Embrapa Agroindustria de Alimentos
//

require("fcs-gerais.php");
require("./include/patrimonio.conf");

// Inicia sessão
session_start();

// Se já estiver logado, redireciona
if (isset($_SESSION['mat'])) {
    header("Location: index.php");
    exit;
}

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $matricula = $_POST['matricula'];
    $senha = $_POST['senha'];

    // Verifica se é o usuário de teste
    if ($matricula == '340044' && $senha != 'admin') {
        $erro = "Senha incorreta";
    } else {
        // Conecta ao banco
        $conn = abre_banco($VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha);

        // Verifica usuário na view_funcionario
        $query = "SELECT $FD_FUNC_MAT, $FD_FUNC_NOME, $FD_FUNC_USERNAME 
                FROM $TB_FUNCIONARIO 
                WHERE $FD_FUNC_MAT = '$matricula' 
                AND $V_FUNC_ATIVO";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $usuario = mysqli_fetch_assoc($result);

            // Verifica perfil de acesso
            $query_acesso = "SELECT TipoUsuario 
                            FROM $TB_ACESSO 
                            WHERE MatFunc = '$matricula'";
            
            $result_acesso = mysqli_query($conn, $query_acesso);
            $perfil = 1; // Perfil padrão

            if ($result_acesso && mysqli_num_rows($result_acesso) > 0) {
                $acesso = mysqli_fetch_assoc($result_acesso);
                $perfil = $acesso['TipoUsuario'];
            }

            // Cria sessão
            $_SESSION['mat'] = $usuario[$FD_FUNC_MAT];
            $_SESSION['nome'] = $usuario[$FD_FUNC_NOME];
            $_SESSION['perfil'] = $perfil;

            // Redireciona para index
            header("Location: index.php");
            exit;

        } else {
            $erro = "Matrícula inválida";
        }
    }
}

// Página de login
mc_simples();
?>

<html>
<head>
    <title>Login - Sistema de Controle de Patrimônio - <?php echo $VAR_Versao; ?></title>
    <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
    <style>
        body { font-family: Arial, sans-serif; }
        .login-box { 
            width: 300px; 
            margin: 100px auto; 
            padding: 20px; 
            border: 1px solid #ccc;
            background: #f9f9f9;
        }
        .form-group { margin-bottom: 15px; }
        input[type='text'], input[type='password'] { 
            width: 100%; 
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
        }
        input[type='submit'] { 
            background: #4CAF50; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            cursor: pointer;
            width: 100%;
        }
        .error { 
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='login-box'>
        <center>
            <h2>Sistema de Controle de Patrimônio</h2>
            <h3><?php echo $VAR_Unidade; ?></h3>
            <p>Versão: <?php echo $VAR_Versao; ?></p>
        </center>

        <?php if (isset($erro)): ?>
            <div class="error"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method='post' action='login.php'>
            <div class='form-group'>
                Matrícula:<br>
                <input type='text' name='matricula' required>
            </div>
            <div class='form-group'>
                Senha:<br>
                <input type='password' name='senha' required>
            </div>
            <input type='submit' value='Entrar'>
        </form>
    </div>
</body>
</html>
