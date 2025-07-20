<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Listagem de bens patrimoniais por local
//	Concluído em 25/08/2003
//	ALTERADO: 15/12/2008
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//
//	Alterado por Adil em 15/01/2010
//	====================
//	Local agora é Predio + Local
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

	mc_dados( "Bens patrimoniais por local" );

	//
	//	Pega os nomes dos locais
	//
	$qLocal = "select a.$FD_PREDIO_DESC, b.*
			from $TB_PREDIO a, $TB_LOCAL b
			where a.$FD_PREDIO_COD = b.$FD_LOCAL_PREDIO
			order by a.$FD_PREDIO_DESC, b.$FD_LOCAL_DESC";

	$rLocal = mysql_query( $qLocal );
	if ( !$rLocal ) {
		trata_erro( "Não foi possível acessar a tabela de locais." );

	} elseif ( mysql_num_rows( $rLocal ) == 0 ) {
		echo "<CENTER><BR>\n";
		echo "<H3>Atenção</H3>\n";
		echo "A tabela de locais está vazia. Informe ao pessoal da Informática.<BR>\n";
		echo "</CENTER><BR>\n";
		exit;
	}

	//
	//	Monta formulario para pesquisa
	//
	echo "<FORM METHOD='post'>";
	echo "Sr(a). Agente Patrimonial,<BR>\n";
	echo "Selecione o local para impressão da listagem de bens.<BR>\n";
	echo "<CENTER><P>";
	echo "<TABLE BORDER>";
	echo "<TR>";
	echo "<TD VALIGN='top' BGCOLOR='#C0C0C0'><B>Locais:</B></TD>";
	echo "<TD><SELECT NAME='escolhido'>";
		while ( $DadosLocal = mysql_fetch_array( $rLocal ) ) {
			echo "<OPTION VALUE= '$DadosLocal[$FD_LOCAL_COD],$DadosLocal[$FD_PREDIO_DESC],$DadosLocal[$FD_LOCAL_DESC]'>$DadosLocal[$FD_PREDIO_DESC] - $DadosLocal[$FD_LOCAL_DESC]</OPTION>";
		}
	echo "</SELECT></TD>";
	echo "</TR>";
	echo "</TABLE>";
	echo "</P>";
	echo "<INPUT TYPE='reset' NAME='limpa' VALUE='Limpar formulário'>";
	echo "<INPUT TYPE='submit' NAME='montar' VALUE='Montar relatório'>";
	echo "</CENTER>";
	echo "</FORM>";

	exit;
}

mc_simples();
$DadosLocal = explode( "," , $_POST[escolhido] );


//
//	Pega os empregados ATIVOS
//
$qFunc = "select $FD_FUNC_MAT, $FD_FUNC_NOME
		from $TB_FUNCIONARIO
		where $V_FUNC_ATIVO
		order by $FD_FUNC_NOME";

$rFunc = mysql_query( $qFunc );
if ( !$rFunc ) {
	trata_erro( "Não foi possível acessar a tabela de empregados." );
} elseif ( mysql_num_rows( $rFunc ) == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Atenção</H3>\n";
	echo "A tabela de empregados está vazia. Informe ao pessoal da Informática.<BR>\n";
	echo "</CENTER><BR>\n";
	exit;
} elseif ( mysql_num_rows( $rFunc ) >= 0 ) {
	WHILE ( $dFunc = mysql_fetch_array( $rFunc ) ) {
		$Func[$dFunc[$FD_FUNC_MAT]] = $dFunc[$FD_FUNC_NOME];
	}
	mysql_free_result( $rFunc );
}


//
//	Seleciona os bens localizados no local escolhido
//
$qBens = "select *
		from $TB_PATRIMONIO
		where $FD_PAT_LOCAL = '$DadosLocal[0]'
		order by $FD_PAT_NUMPAT, $FD_PAT_INC";

$rBens = mysql_query( $qBens );
if ( !$rBens ) {
	trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
} elseif ( mysql_num_rows( $rBens ) == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Atenção</H3>\n";
	echo "O Empregado não possui bem patrimonial sob sua responsabilidade.<BR>\n";
	echo "</CENTER><BR>\n";
	exit;
}


//
//	Montando o TERMO DE RESPONSABILIDADE
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
echo "<H2>Listagem por local</H2>\n";
echo "</CENTER>";

//
//	Identificacao do local
//
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=8%><B>Local:</B></TD>";
echo "<TD>$DadosLocal[1] - $DadosLocal[2]<BR></TD>";
echo "</TR>";
echo "</TABLE>";


//
//	Cabecalho
//
echo "<P>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=12% BGCOLOR='#C0C0C0'><B>Código</B></TD>";
echo "<TD WIDTH=60% BGCOLOR='#C0C0C0'><B>Descrição</B></TD>";
echo "<TD WIDTH=28% BGCOLOR='#C0C0C0'><B>Responsável</B></TD>";
echo "</TR>";
echo "</TR>";

//
//	Listagem dos bens patrimoniais
//
WHILE ( $temp = mysql_fetch_array( $rBens ) ) {
	echo "<TR>";
	echo "<TD WIDTH=12%>$temp[NumPat]-$temp[Inc]</TD>";
	echo "<TD WIDTH=60%>$temp[Descricao]</TD>";
	echo "<TD WIDTH=28%>" . $Func[$temp[MatResp]] . "</TD>";
	echo "</TR>";
}
echo "</TABLE>";

mr_simples();

?>