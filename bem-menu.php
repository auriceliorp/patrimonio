<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Gerador do menu do sistema
//	Concluído em 25/08/2003
//	Alterado em: 18/09/2008
//	ALTERADO: 15/12/2008
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//


//
// Verifica se tem algum usuario logado no momento
//
session_start();
if ( !isset($_SESSION[mat]) ) {
	die("Voce precisa se logar primeiro!");
}

//
//	Preparar o ambiente
//
include( "fcs-gerais.php" );
include( "./include/patrimonio.conf" );
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );
mc_simples();

$qAcesso = "SELECT TipoUsuario
			FROM $TB_ACESSO
			WHERE MatFunc = '$_SESSION[mat]'";
@$rAcesso = mysql_query( $qAcesso );
$linhas = mysql_num_rows( $rAcesso );
if ( !$linhas || $linhas == 0 ) {
	$acesso = 1;
} else {
	$Dados = mysql_fetch_array( $rAcesso );
	$acesso = $Dados[TipoUsuario];
}

//=====================================================
//
//	OPCOES DO USUARIO
//
//	Montagem do menu
//
echo "<P><B>Usuário</B><BR>";
echo "<HR>";
echo "<a href='./bem-meusbens.php' target ='dados'>Meus bens</a><BR>";
echo "<a href='./bem-transf.php?tipo=pede' target ='dados'>Solicita transf.</a><BR>";
echo "<a href='./bem-pesquisa.php?tipo=transf' target ='dados'>Aceita transf.</a><BR>";
echo "<a href='./bem-local.php' target ='dados'>Altera local</a><BR>";
echo "<a href='./bem-aliena-sol.php' target ='dados'>Solicita alienação</a><BR>";
echo "<a href='./bem-cancela.php?tipo=inicio' target ='dados'>Cancelamento</a><BR>";


//===========================================
//
//	OPCOES DO AGENTE PATRIMONIAL
//
//	2 - Administrador
//	6- Patrimonio
//
if ( $acesso == $PERFIL_Agente ) {
	echo "<P><B>Patrimônio</B><BR>";
	echo "<HR>";
	echo "<a href='./bem-pesquisa.php?tipo=transf-pendente' target ='dados'>Transf. pendentes</a><BR>";
	echo "<a href='./bem-transf-agente.php?user=$acesso' target ='dados'>Realiza transf.</a><BR>";
	echo "<a href='./bem-pesquisa.php?tipo=aliena-conf' target ='dados'>Aceita alienação</a><BR>";
	echo "<a href='./bem-pesquisa.php?tipo=aliena-fim' target ='dados'>Finaliza alienação</a><BR>";
	echo "<a href='./bem-local-agente.php' target ='dados'>Troca de Local</a><BR>";
	echo "<a href='./bem-trocasituacao.php' target ='dados'>Troca de situação</a> - NOVO<BR>";
}
echo "</P>";


//===========================================
//
//	OPCOES DA COMISSAO DE INVENTARIO
//
//	6 - Patrimonio
//	26 - Membros da Comissao de Inventario
//
if ( $acesso == $PERFIL_Agente || $acesso == $PERFIL_Inventario ) {
	echo "<P><B>Comissão</B><BR>";
	echo "<HR>";
	echo "<a href='./bem-local-comissao.php' target ='dados'>Alteração de local</a><BR>";
	echo "<a href='./bem-pesquisa.php?tipo=transf-pendente' target ='dados'>Transf. pendentes</a><BR>";
}
echo "</P>";


//===============================================
//
//	RELATORIOS
//
//	2 - Administrador
//	6- Patrimonio
//	7- Chefia
//
if ( $acesso == $PERFIL_Agente || $acesso == $PERFIL_Chefia ) {
	echo "<P><B>Relatório</B><BR>";
		echo "<HR>";
	echo "<a href='./bem-rel1.php' target ='dados'>Transf. concluídas</a><BR>";
	echo "<a href='./bem-rel2.php' target ='dados'>Termo individual</a><BR>";
	echo "<a href='./bem-rel3.php' target ='dados'>Bens por situação</a><BR>";
	echo "<a href='./bem-rel6.php' target ='dados'>Bens por tipo</a><BR>";
	echo "<a href='./bem-rel4.php' target ='dados'>Listagem geral</a><BR>";
	echo "<a href='./bem-rel5.php' target ='dados'>Bens por área</a><BR>";
	echo "<a href='./bem-rel7pdf.php' target ='novo'>Completo por local</a><BR>";
	echo "<a href='./bem-rel8-ativo.php' target ='dados'>Empreg. sem bens</a><BR>";
	echo "<a href='./bem-rel8-inativo.php' target ='dados'>Inativos com bens</a><BR>";
	echo "<a href='./bem-rel9.php' target ='dados'>Bens por local</a><BR>";
	if ( $_SESSION[mat] == '262177' ) {
		echo "<a href='./TESTE-bem-rel2pdf.php?nome=$_SESSION[nome]&mat=$_SESSION[mat]' target ='novo'>TESTE-Termo de respons.</a><BR>";
	}
	echo "<P>";
}


//================================================
//
//	PESQUISA POR BEM
//
//	2 - Administrador
//	6- Patrimonio
//
if ( $acesso == $PERFIL_Agente ) {
	echo "<P><B>Cadastro</B><BR>";
	echo "<HR>";
	echo "Digite o número do bem a pesquisar:";
	echo "<FORM ACTION='./bem-cad.php' TARGET='dados' METHOD='post'>";
	echo "<INPUT TYPE='text' NAME='numpat' SIZE='7' MAXLENGTH=7>";
	echo "<INPUT TYPE='text' NAME='inc' SIZE='3'  MAXLENGTH=3 VALUE='000'>";
	echo "<INPUT TYPE='submit' NAME='submit' VALUE='Pesquisa'>";
	echo "</FORM>";
	echo "<P>";
}
	
//echo "</BODY>";
mr_simples();

?>