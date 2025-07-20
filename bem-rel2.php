<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Gerador do Termo de Responsabilidade por usuario
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
//	Se está entrando agora E a solicitação não vier do relatório para INATIVOS COM BENS (bem-rel8-inativos.php)
//		ESCOLHE EMPREGADO
//
if ( !$_POST['montar'] && !isset( $_GET['escolhido'] ) ) {

	mc_dados( "Impressão do Termo de Responsabilidade" );

	//
	//	Pega os empregados e seus locais de trabalho
	//
	$qFunc = "select $TB_FUNCIONARIO.$FD_FUNC_MAT, $TB_FUNCIONARIO.$FD_FUNC_NOME, $TB_LOCAL.$FD_LOCAL_DESC
			from $TB_FUNCIONARIO, $TB_LOCAL
			where $V_FUNC_ATIVO and
				$TB_FUNCIONARIO.Local = $TB_LOCAL.$FD_LOCAL_COD
			order by $TB_FUNCIONARIO.$FD_FUNC_NOME";

	$rFunc = mysql_query( $qFunc );
	if ( !$rFunc ) {
		trata_erro( "Não foi possível acessar a tabela de empregados." );
	} elseif ( mysql_num_rows( $rFunc ) == 0 ) {
		echo "<CENTER><BR>\n";
		echo "<H3>Atenção</H3>\n";
		echo "A tabela de empregados está vazia. Informe ao pessoal da Informática.<BR>\n";
		echo "</CENTER><BR>\n";
		exit;
	}

	//
	//	Monta formulario para pesquisa
	//
	echo "<FORM METHOD='post'>";
	echo "Sr(a). Agente patrimonial,<BR>\n";
	echo "Selecione o empregado para a impressão do TERMO DE RESPONSABILIDADE.<BR>\n";
	echo "<CENTER><P>";
	echo "<TABLE BORDER>";
	echo "<TR>";
	echo "<TD VALIGN='top' BGCOLOR='#C0C0C0'><B>Empregados:</B></TD>";
	echo "<TD><SELECT NAME='escolhido'>";
		while ( $DadosFunc = mysql_fetch_array( $rFunc ) ) {
//			echo "<OPTION VALUE= '$DadosFunc[Matricula],$DadosFunc[Nome],$DadosFunc[DescLocal]'>$DadosFunc[Nome]</OPTION>";
			echo "<OPTION VALUE= '$DadosFunc[Matricula],$DadosFunc[Nome]'>$DadosFunc[Nome]</OPTION>";
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


//
//	Verificando se o pedido veio desta página ou do relatório de INATIVOS com BENS
//
if ( isset( $_POST['escolhido']) ) {
	$DadosFunc = explode( "," , $_POST['escolhido'] );
} elseif ( isset( $_GET['escolhido']) ) {
	$DadosFunc = explode( "," , $_GET['escolhido'] );
} else {
	trata_erro( "Não foi selecionado nenhum empregado para impressão." );
}


//
//	Pega os nomes dos locais
//
$qLocal = "select a.$FD_PREDIO_DESC, b.$FD_LOCAL_COD, b.$FD_LOCAL_DESC
		from $TB_PREDIO a, $TB_LOCAL b
		where a.$FD_PREDIO_COD = b.$FD_LOCAL_PREDIO";
$rLocal = mysql_query( $qLocal );
if ( !$rLocal ) {
	trata_erro( "Não foi possível acessar a tabela de locais." );
} elseif ( mysql_num_rows( $rLocal ) == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Atenção</H3>\n";
	echo "A tabela de locais está vazia. Informe ao pessoal da Informática.<BR>\n";
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
//	Seleciona os bens sob a responsabilidade do usuario escolhido
//
$qBens = "select $FD_PAT_NUMPAT, $FD_PAT_INC, $FD_PAT_DESC, $FD_PAT_LOCAL
		from $TB_PATRIMONIO
		where $FD_PAT_RESP = '$DadosFunc[0]'
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
echo "<H2>TERMO DE RESPONSABILIDADE</H2>\n";
echo "</CENTER>";

echo "<font size=-1>";

//
//	Identificacao do empregado
//
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=8%><font size=-1><B>Empregado:</B></font></TD>";
echo "<TD><font size=-1>$DadosFunc[0] - $DadosFunc[1]<BR></TD>";
echo "</TR>";
echo "</TABLE>";

//
//	Cabecalho da tabela
//
echo "<P>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH='11%' BGCOLOR='#C0C0C0'><font size=-1><B>Código</B></TD>";
echo "<TD WIDTH='59%' BGCOLOR='#C0C0C0'><font size=-1><B>Descrição</B></TD>";
echo "<TD WIDTH='35%' BGCOLOR='#C0C0C0'><font size=-1><B>Local</B></TD>";
echo "</TR>";

//
//	Listagem dos bens patrimoniais
//
WHILE ( $dBens = mysql_fetch_array( $rBens ) ) {
	echo "<TR>";
	echo "<TD WIDTH='11%'><font size=-1>$dBens[NumPat]-$dBens[Inc]</TD>";
	echo "<TD WIDTH='59%'><font size=-1>$dBens[Descricao]</TD>";
	echo "<TD WIDTH='35%'><font size=-1>" . $Local[$dBens[$FD_PAT_LOCAL]] . "</TD>";
	echo "</TR>";
}
echo "</TABLE>";

//
//	Rodapé
//
//echo "<BR><BR><BR><BR><BR><BR><BR><BR><BR><BR>";

echo "<HR>";
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH='7%' VALIGN=top><font size=-1><B>OBS:</B></TD>";
echo "<TD WIDTH='93%'><font size=-1>em caso de dano ou desaparecimento de bens, avisar imediatamente ao Agente Patrimonial ou à Chefia imediata, bem como promover a comunicação formal do fato ao SPM, por meio do formulário <U>Comunicação de Dano Patrimonial</U>. Não efetuar a transferência de qualquer bem sem a prévia formalização regulamentar.</TD>";
echo "</TR>";
echo "</TABLE>";

echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=40% ALIGN=center><HR><B>Agente patrimonial</B><HR></TD>";
echo "<TD WIDTH=10%></TD>";
echo "<TD WIDTH=50% ALIGN=center><HR><B>Usuário dos bens</B><HR></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD WIDTH=40% ALIGN=center></TD>";
echo "<TD WIDTH=10%></TD>";
echo "<TD WIDTH=50% ALIGN=center>
<P>Declaro pelo presente documento de responsabilidade que conferi e recebi os bens acima relacionados que ficarão sob minha guarda.</TD>";
echo "</TR>";
echo "<TR>";
echo "<TD WIDTH=40% ALIGN=left VALIGN=top><P>Data: " . troca_data( date("Y/m/d") ) . "</P></TD>";
echo "<TD WIDTH=10%></TD>";
echo "<TD WIDTH=50% ALIGN=left VALIGN=top><P>Data: " . troca_data( date("Y/m/d") ) . "</P><BR></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD WIDTH=40% VALIGN=top><HR></TD>";
echo "<TD WIDTH=10%></TD>";
echo "<TD WIDTH=50% ALIGN=center><P><HR>$DadosFunc[1]";
echo "</P></TD>";
echo "</TR>";
echo "</TABLE>";
echo "</P>";

mr_simples();

echo "</font>";

?>