<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Listagem geral por número de patrimônio
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
mc_simples();

//
//	Retira os bens já excluídos
//
$excluidos = " a.CodLocal != $VAR_Excluido ";

//
//	Seleciona todos os bens
//
$qBens = "select a.$FD_PAT_NUMPAT, a.$FD_PAT_INC, a.$FD_PAT_DESC, b.$FD_LOCAL_DESC, c.$FD_FUNC_NOME, d.$FD_PREDIO_DESC
		from $TB_PATRIMONIO a, $TB_LOCAL b, $TB_FUNCIONARIO c, $TB_PREDIO d
		where a.$FD_PAT_NUMPAT != '0000000' and
			a.$FD_PAT_LOCAL = b.$FD_LOCAL_COD and
			b.$FD_LOCAL_PREDIO = d.$FD_PREDIO_COD and
			a.$FD_PAT_RESP = c.$FD_FUNC_MAT and
			$excluidos
		order by a.$FD_PAT_NUMPAT, a.$FD_PAT_INC";

$rBens = mysql_query( $qBens );
if ( !$rBens ) {
	trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
} elseif ( mysql_num_rows( $rBens ) == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Atenção</H3>\n";
	echo "A tabela de empregados está vazia. Informe ao pessoal da Informática.<BR>\n";
	echo "</CENTER><BR>\n";
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
echo "<H2>Listagem geral dos bens patrimoniais</H2>\n";
echo "</CENTER>";


//
//	Cabecalho
//
echo "<P>";
echo "<TABLE BORDER WIDTH='100%'>";
echo "<TR>";
echo "<TD WIDTH='8%' BGCOLOR='#C0C0C0'><B>Código</B></TD>";
echo "<TD WIDTH='55%' BGCOLOR='#C0C0C0'><B>Descrição</B></TD>";
echo "<TD WIDTH='25%' BGCOLOR='#C0C0C0'><B>Local</B></TD>";
echo "<TD WIDTH='12%' BGCOLOR='#C0C0C0'><B>Responsável</B></TD>";
echo "</TR>";

//
//	Listagem dos bens patrimoniais
//
WHILE ( $temp = mysql_fetch_array( $rBens ) ) {
	echo "<TR>";
	echo "<TD WIDTH='8%'>$temp[NumPat]-$temp[Inc]</TD>";
	echo "<TD WIDTH='55%'>$temp[Descricao]</TD>";
	echo "<TD WIDTH='25%'>$temp[$FD_PREDIO_DESC] - $temp[$FD_LOCAL_DESC]</TD>";
	echo "<TD WIDTH='12%'>$temp[Nome]</TD>";
	echo "</TR>";
}
echo "</TABLE>";

mr_simples();

?>