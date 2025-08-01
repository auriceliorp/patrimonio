<?php
//
// Sistema: Bens patrimoniais
// Modulo: Login e Autenticação
// Autor: Adil D. Pinto Jr.
// Embrapa Agroindustria de Alimentos
//

// Inicia a sessão
session_start();

// IMPORTANTE: Não pode haver nenhum espaço ou linha em branco antes do <?php
require("./include/patrimonio.conf");
require("fcs-gerais.php");

// Se já estiver logado, redireciona
if (isset($_SESSION['mat'])) {
    header("Location: index.php");
    exit;
}

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $matricula = trim($_POST['matricula']);
    $senha = trim($_POST['senha']);

    // Verifica se é um dos usuários de teste
    if ($matricula == '340044' || $matricula == '999999') {
        $usuario_teste = false;
        
        // Usuário de teste original (Chefia)
        if ($matricula == '340044' && $senha == 'admin') {
            $usuario_teste = [
                'mat' => '340044',
                'nome' => 'Usuário de Teste - Chefia',
                'perfil' => $PERFIL_Chefia
            ];
        }
        
        // Super usuário
        if ($matricula == '999999' && $senha == 'super123') {
            $usuario_teste = [
                'mat' => '999999',
                'nome' => 'Super Usuário',
                'perfil' => $PERFIL_Inventario
            ];
        }

        if ($usuario_teste) {
            session_regenerate_id(true); // Segurança adicional
            $_SESSION['mat'] = $usuario_teste['mat'];
            $_SESSION['nome'] = $usuario_teste['nome'];
            $_SESSION['perfil'] = $usuario_teste['perfil'];
            $_SESSION['super_user'] = ($usuario_teste['mat'] == '999999');
            
            header("Location: index.php");
            exit;
        } else {
            $erro = "Senha incorreta";
        }
    } else {
        // Conecta ao banco
        $conn = abre_banco($VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha, $VAR_Porta);

        // Verifica usuário na tabela de funcionários
        $matricula = escape_string($conn, $matricula);
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
            session_regenerate_id(true); // Segurança adicional
            $_SESSION['mat'] = $usuario[$FD_FUNC_MAT];
            $_SESSION['nome'] = $usuario[$FD_FUNC_NOME];
            $_SESSION['perfil'] = $perfil;

            // Redireciona para index
            header("Location: index.php");
            exit;

        } else {
            $erro = "Matrícula inválida";
        }
        mysqli_close($conn);
    }
}

// HTML da página de login
?><!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema de Controle de Patrimônio - <?php echo $VAR_Versao; ?></title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
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
        input[type='submit']:hover {
            background: #45a049;
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
            <div class="error"><?php echo htmlspecialchars($erro); ?></div>
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

