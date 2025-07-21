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
		echo "<script>window.location.href = 'login.php';</script>";
		exit;
	}
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
			width: 250px;
			background: #f9f9f9;
			padding: 15px;
			border: 1px solid #ddd;
			overflow: auto; /* Para caso o menu seja muito longo */
		}
		.content {
			flex: 1;
			padding: 15px;
			border: 1px solid #ddd;
		}
		/* Estilização dos links do menu que virão do bem-menu.php */
		.menu a {
			display: block;
			padding: 6px 0;
			color: #333;
			text-decoration: none;
			font-size: 14px;
		}
		.menu a:hover {
			color: #000;
			background: #eee;
			padding-left: 5px;
		}
		.menu hr {
			border: none;
			border-top: 1px solid #ddd;
			margin: 10px 0;
		}
		.menu b {
			display: block;
			margin-top: 15px;
			margin-bottom: 5px;
			color: #666;
		}
		/* Estilo para o formulário de pesquisa */
		.menu form {
			margin-top: 10px;
		}
		.menu input[type="text"] {
			padding: 4px;
			margin: 2px 0;
			border: 1px solid #ddd;
		}
		.menu input[type="submit"] {
			background: #4CAF50;
			color: white;
			border: none;
			padding: 5px 10px;
			cursor: pointer;
			margin-top: 5px;
		}
		.menu input[type="submit"]:hover {
			background: #45a049;
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
			<?php include("bem-menu.php"); ?>
		</div>

		<div class="content">
			<h3>Bem-vindo ao Sistema de Controle de Patrimônio</h3>
			<p>Selecione uma opção no menu lateral para começar.</p>
		</div>
	</div>
</body>
</html>
