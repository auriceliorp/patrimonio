<?php
	//
	// Sistema: Bens patrimoniais
	// Modulo: Principal
	// Concluído em 25/08/2003
	// Autor: Adil D. Pinto Jr.
	// Embrapa Agroindustria de Alimentos
	//

	session_start();
	require("fcs-gerais.php");
	require("./include/patrimonio.conf");

	// Se não estiver logado, redireciona para login
	if (!isset($_SESSION['mat'])) {
		header("Location: login.php");
		exit;
	}

	// Página inicial do sistema
?>
<!DOCTYPE html>
<html>
<head>
	<title>Sistema de Controle de Patrimônio - <?php echo $VAR_Versao; ?></title>
	<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
	<style>
		body { 
			font-family: Arial, sans-serif;
			margin: 0;
			padding: 20px;
		}
		.header {
			background: #f5f5f5;
			padding: 20px;
			margin-bottom: 20px;
			border-bottom: 1px solid #ddd;
		}
		.container {
			display: flex;
			gap: 20px;
		}
		.menu {
			width: 200px;
			background: #f9f9f9;
			padding: 15px;
			border: 1px solid #ddd;
		}
		.content {
			flex: 1;
			padding: 15px;
			border: 1px solid #ddd;
		}
		.menu a {
			display: block;
			padding: 8px 0;
			color: #333;
			text-decoration: none;
		}
		.menu a:hover {
			color: #000;
			background: #eee;
		}
	</style>
</head>
<body>
	<div class="header">
		<h2>Sistema de Controle de Patrimônio</h2>
		<h3><?php echo $VAR_Unidade; ?></h3>
		<p>Usuário: <?php echo $_SESSION['nome']; ?></p>
	</div>

	<div class="container">
		<div class="menu">
			<h3>Menu</h3>
			<a href='bem-meusbens.php'>Meus bens</a>
			<a href='bem-transf.php?tipo=pede'>Solicitar transferência</a>
			<a href='bem-pesquisa.php?tipo=transf'>Aceitar transferência</a>
			<a href='bem-local.php'>Alterar local</a>
			<a href='bem-aliena-sol.php'>Solicitar alienação</a>
			<a href='bem-cancela.php?tipo=inicio'>Cancelamento</a>
			<?php if ($_SESSION['perfil'] >= 6): ?>
			<h3>Agente Patrimonial</h3>
			<a href='bem-local-agente.php'>Alterar local (Agente)</a>
			<a href='bem-transf-agente.php'>Transferência (Agente)</a>
			<a href='bem-trocasituacao.php'>Trocar situação</a>
			<?php endif; ?>
		</div>

		<div class="content">
			<h3>Bem-vindo ao Sistema de Controle de Patrimônio</h3>
			<p>Selecione uma opção no menu lateral para começar.</p>
		</div>
	</div>
</body>
</html>
