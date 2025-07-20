<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Apresentacao dos bens patrimoniais do usuario logado
//	Conclu�do em 25/08/2003
//	ALTERADO: 15/12/2008
//	ALTERADO: 05/01/2010
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
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );
mc_dados( "Listagem dos bens patrimoniais sob sua responsabilidade" );


//
//	Pega os nomes dos locais
//
$qLocal = "select a.$FD_PREDIO_DESC, b.$FD_LOCAL_COD, b.$FD_LOCAL_DESC
		from $TB_PREDIO a, $TB_LOCAL b
		where a.$FD_PREDIO_COD = b.$FD_LOCAL_PREDIO";
$rLocal = mysql_query( $qLocal );
if ( !$rLocal ) {
	trata_erro( "N�o foi poss�vel acessar a tabela de locais." );
} elseif ( mysql_num_rows( $rLocal ) == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Aten��o</H3>\n";
	echo "A tabela de locais est� vazia. Informe ao pessoal da Inform�tica.<BR>\n";
	echo "</CENTER><BR>\n";
	exit;
} elseif ( mysql_num_rows( $rLocal ) >= 0 ) {
	WHILE ( $temp = mysql_fetch_array( $rLocal ) ) {
		$cod = $temp[$FD_LOCAL_COD];
		$Local[$cod] = $temp[$FD_PREDIO_DESC] . " - " . $temp[$FD_LOCAL_DESC] ;
	}
	mysql_free_result( $rLocal );
}

//
//	Seleciona os bens sob sua responsabilidade
//
$qBens = "select $FD_PAT_NUMPAT, $FD_PAT_INC, $FD_PAT_DESC, $FD_PAT_LOCAL
		from $TB_PATRIMONIO
		where $FD_PAT_RESP = '$_SESSION[mat]'
		order by $FD_PAT_NUMPAT, $FD_PAT_INC";

$rBens = mysql_query( $qBens );
if ( !$rBens ) {
	trata_erro( "N�o foi poss�vel acessar a tabela de bens patrimoniais." );
} elseif ( mysql_num_rows( $rBens ) == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Aten��o</H3>\n";
	echo "N�o h� bens patrimoniais sob sua responsabilidade.<BR>\n";
	echo "</CENTER><BR>\n";
	exit;
}


//
//	Mostrando a listagem de bens
//
//

//
//	Identificacao do empregado
//
echo "<TABLE WIDTH=650>";
echo "<TR>";
echo "<TD WIDTH=8%><B>Nome:</B></TD>";
echo "<TD>$_SESSION[mat] - $_SESSION[nome]<BR></TD>";
echo "</TABLE>";

//
//	Cabecalho
//
echo "<P>";
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=12% BGCOLOR='#C0C0C0'><B>C�digo</B></TD>";
echo "<TD WIDTH=60% BGCOLOR='#C0C0C0'><B>Descri��o</B></TD>";
echo "<TD WIDTH=28% BGCOLOR='#C0C0C0'><B>Local</B></TD>";
echo "</TR>";

//
//	Dados
//
WHILE ( $dBens = mysql_fetch_array( $rBens ) ) {
	echo "<TR>";
	echo "<TD WIDTH=12% VALIGN=top>$dBens[$FD_PAT_NUMPAT]-$dBens[$FD_PAT_INC]</TD>";
	echo "<TD WIDTH=60% VALIGN=top>$dBens[$FD_PAT_DESC]</TD>";
	echo "<TD WIDTH=28% VALIGN=top>" . $Local[$dBens[$FD_PAT_LOCAL]] . "</TD>";
	echo "</TR>";
}
echo "</TABLE>";

mr_simples();

?>