<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Listagem - Empregados ATIVOS sem bens patrimoniais
//	Concluído em 12/04/2004
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
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );
mc_simples();


//
//	Seleciona empregados sem bens patrimoniais
//
$qFunc = "select $TB_FUNCIONARIO.$FD_FUNC_MAT, $TB_FUNCIONARIO.$FD_FUNC_NOME, count($TB_PATRIMONIO.$FD_PAT_NUMPAT)
		from $TB_FUNCIONARIO left join $TB_PATRIMONIO 
			on $TB_FUNCIONARIO.$FD_FUNC_MAT = $TB_PATRIMONIO.$FD_PAT_RESP
		where $V_FUNC_ATIVO
		group by $TB_FUNCIONARIO.$FD_FUNC_NOME
		having count($TB_PATRIMONIO.$FD_PAT_NUMPAT) = 0";

@$rFunc = mysql_query( $qFunc );
if ( !$rFunc ) {
	trata_erro( "Não foi possível acessar a tabela de bens patrimoniais e/ou de funcionários. Tente mais tarde!" );
} elseif ( mysql_num_rows( $rFunc ) == 0 ) {
	echo "<CENTER><BR>";
	echo "<H3>Atenção</H3>";
	echo "Não há empregados sem bens patrimoniais sob sua responsabilidade.";
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
echo "<H2>Empregados sem bens patrimoniais sob sua responsabilidade</H2>\n";
echo "</CENTER>";


//
//	Cabecalho
//
echo "<P>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=15% BGCOLOR='#C0C0C0' align='center'><B>Matrícula</B></TD>";
echo "<TD WIDTH=85% BGCOLOR='#C0C0C0'><B>Nome</B></TD>";
echo "</TR>";

//
//	Listagem dos bens patrimoniais
//
WHILE ( $dFunc = mysql_fetch_array( $rFunc ) ) {
		echo "<TR>";
		echo "<TD WIDTH=15% align='center'>$dFunc[$FD_FUNC_MAT]</TD>";
		echo "<TD WIDTH=85%>$dFunc[$FD_FUNC_NOME]</TD>";
		echo "</TR>";
}
echo "</TABLE>";

mr_simples();

?>