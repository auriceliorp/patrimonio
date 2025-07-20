<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Listagem de bens patrimoniais por situacao
//	Concluído em 25/08/2003
//	ALTERADO: 15/12/2008
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//


//
//	Verifica se tem algum usuario logado no momento
//
session_start();
if ( !isset($_SESSION[mat]) ) {
	die("Voce precisa se logar primeiro!");
}

//
//	Prepara ambiente
//
require("fcs-gerais.php");
require("./include/patrimonio.conf");
abre_banco( $VAR_Banco, $VAR_UsuarioLeitura, $VAR_Servidor, $VAR_SenhaLeitura );


//
//	Se esta entrando agora
//	ESCOLHE EMPREGADO
//
if ( !$_POST[montar] ) {

	mc_dados( "Bens patrimoniais em situacao especial" );

	//
	//	Pega os nomes das situacoes
	//
	$qSitua = "select *
			from $TB_SITUACAO
			order by $FD_SIT_DESC";

	$rSitua = mysql_query( $qSitua );
	if ( !$rSitua ) {
		trata_erro( "Não foi possível acessar a tabela de situações." );
	} elseif ( mysql_num_rows( $rSitua ) == 0 ) {
		echo "<CENTER><BR>\n";
		echo "<H3>Atenção</H3>\n";
		echo "A tabela de situações está vazia. Informe ao pessoal da Informática.<BR>\n";
		echo "</CENTER><BR>\n";
		exit;
	}

	//
	//	Monta formulario para pesquisa
	//
	echo "<FORM METHOD='post'>";
	echo "Sr(a). Agente Patrimonial,<BR>\n";
	echo "Selecione a situação para impressão da listagem de bens.<BR>\n";
	echo "<CENTER><P>";
	echo "<TABLE BORDER>";
	echo "<TR>";
	echo "<TD VALIGN='top' BGCOLOR='#C0C0C0'><B>Situações:</B></TD>";
	echo "<TD><SELECT NAME='escolhido'>";
		while ( $Dados = mysql_fetch_array( $rSitua ) ) {
			echo "<OPTION VALUE= '$Dados[$FD_SIT_COD],$Dados[$FD_SIT_DESC]'>$Dados[$FD_SIT_DESC]</OPTION>";
		}
	echo "</SELECT></TD>";
	echo "</TR>";
	echo "</TABLE>";
	echo "</P>";
	echo "<INPUT TYPE='reset' NAME='limpa' VALUE='Limpar formulário'>";
	echo "<INPUT TYPE='submit' NAME='montar' VALUE='Montar relatório'>";
	echo "</CENTER>";
	echo "</FORM>";

	mysql_free_result( $rSitua );
	exit;
}

mc_simples();
$Dados = explode( "," , $_POST[escolhido] );

//
//	Seleciona os bens em uma determinada situacao
//
$qBens = "select a.*, b.$FD_LOCAL_DESC, c.$FD_PREDIO_DESC
		from $TB_PATRIMONIO a, $TB_LOCAL b, $TB_PREDIO c
		where a.$FD_PAT_SITUACAO = '$Dados[0]' and
			a.$FD_PAT_LOCAL = b.$FD_LOCAL_COD and
			b.$FD_LOCAL_PREDIO = c.$FD_PREDIO_COD
		order by $FD_PAT_NUMPAT, $FD_PAT_INC";

$rBens = mysql_query( $qBens );
if ( !$rBens ) {
	trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
} elseif ( mysql_num_rows( $rBens ) == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Atenção</H3>\n";
	echo "Não existe bem patrimonial nesta situação.<BR>\n";
	echo "</CENTER><BR>\n";
	exit;
}


//
//	Montando Relatorio
//
//

//
//	Cabecalho do relatorio
//
echo "<CENTER><IMG SRC='/figuras/embrapa.gif'></CENTER>";
echo "<P>";
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD COLSPAN=2><B>Empresa Brasileira de Pesquisa Agropecuária - EMBRAPA</B></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD COLSPAN=2><B>$VAR_Unidade</B></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD WIDTH='80%'><B>Gestão de Patrimônio</B></TD>";
echo "<TD WIDTH='20%' ALIGN=right>Data: " . troca_data( date ("Y/m/d" ) ) . "</TD>";
echo "</TR>";
echo "</TABLE>";
echo "<HR>";
echo "<CENTER>";
echo "<H2>Listagem por situação</H2>\n";
echo "</CENTER>";

//
//	Identificacao do local
//
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=8%><B>Situação:</B></TD>";
echo "<TD>$Dados[1]<BR></TD>";
echo "</TR>";
echo "</TABLE>";


//
//	Cabecalho
//
echo "<P>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=12% BGCOLOR='#C0C0C0'><B>Código</B></TD>";
echo "<TD WIDTH=55% BGCOLOR='#C0C0C0'><B>Descrição</B></TD>";
echo "<TD WIDTH=33% BGCOLOR='#C0C0C0'><B>Local</B></TD>";
echo "</TR>";

//
//	Listagem dos bens patrimoniais
//
WHILE ( $temp = mysql_fetch_array( $rBens ) ) {
	echo "<TR>";
	echo "<TD WIDTH=12%>$temp[NumPat]-$temp[Inc]</TD>";
	echo "<TD WIDTH=55%>$temp[Descricao]</TD>";
	echo "<TD WIDTH=33%>$temp[$FD_PREDIO_DESC] - $temp[$FD_LOCAL_DESC]</TD>";
	echo "</TR>";
}
echo "</TABLE>";

mr_simples();

?>