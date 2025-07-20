<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Listagem - Quantidade de bens por local
//	Concluído em 10/05/2005
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
include( "fcs-gerais.php" );
include( "./include/patrimonio.conf" );
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );
mc_simples();


//
//	Retira os bens já excluídos
//
//$excluidos = " a.CodLocal != $VAR_Excluido ";

//
//	Realiza somatorio dos bens por local
//
//$qBem = "select count(a.$FD_PAT_COD) as Total, b.$FD_LOCAL_DESC
//		from $TB_PATRIMONIO a, $TB_LOCAL b
//		where a.$FD_PAT_LOCAL = b.$FD_LOCAL_COD and
//			$excluidos
//		GROUP BY b.CodLocal
//		ORDER BY b.DescLocal";
//$qBem = "select count(a.$FD_PAT_COD) as Total, b.$FD_LOCAL_DESC
//		from $TB_PATRIMONIO a, $TB_LOCAL b
//		where a.$FD_PAT_LOCAL = b.$FD_LOCAL_COD and
//			a.$FD_PAT_LOCAL != $VAR_Excluido 
//		group by b.$FD_LOCAL_COD
//		order by b.$FD_LOCAL_DESC";
$qBem = "select count(a.$FD_PAT_COD) as Total, b.$FD_LOCAL_DESC, c.$FD_PREDIO_DESC
		from $TB_PATRIMONIO a, $TB_LOCAL b, $TB_PREDIO c
		where a.$FD_PAT_LOCAL = b.$FD_LOCAL_COD and
			b.$FD_LOCAL_PREDIO = $FD_PREDIO_COD and
			a.$FD_PAT_LOCAL != $VAR_Excluido 
		group by c.$FD_PREDIO_COD,b.$FD_LOCAL_COD
		order by c.$FD_PREDIO_DESC,b.$FD_LOCAL_DESC";

@$rBem = mysql_query( $qBem );
if ( !$rBem ) {
	trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
} elseif ( mysql_num_rows( $rBem ) == 0 ) {
	echo "<CENTER><BR>";
	echo "<H3>Atenção</H3>";
	echo "Não há bens patrimoniais cadastro na tabela.";
	echo "</CENTER>";
	exit;
}


//
//	Montando a LISTAGEM GERAL
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
echo "<H2>Quantidade de bens patrimoniais por local</H2>\n";
echo "</CENTER>";


//
//	Cabecalho
//
echo "<P>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=80% BGCOLOR='#C0C0C0'><B>Área/local</B></TD>";
echo "<TD WIDTH=20% BGCOLOR='#C0C0C0' ALIGN='center'><B>Quantidade</B></TD>";
echo "</TR>";

//
//	Listagem dos bens patrimoniais
//
WHILE ( $temp = mysql_fetch_array( $rBem ) ) {
		echo "<TR>";
		echo "<TD WIDTH=80%>$temp[$FD_PREDIO_DESC] - $temp[$FD_LOCAL_DESC]</TD>";
		echo "<TD WIDTH=20% ALIGN='center'>$temp[Total]</TD>";
		echo "</TR>";
		$Total = $Total + $temp[Total];
}

echo "<TR>";
echo "<TD WIDTH=80%><B>Total:</B></TD>";
echo "<TD WIDTH=20% ALIGN='center'>$Total</TD>";
echo "</TR>";

echo "</TABLE>";

mr_simples();

?>