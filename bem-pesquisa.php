<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Pesquisa para Aceitar e Consolidar transferencias, alienacoes, cess�es e roubos
//	Conclu�do em 25/08/2003
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//


//
//	Verifica se tem algum usuario logado
//
session_start();
if ( !isset($_SESSION[mat]) ) {
	die("Voc� precisa se logar primeiro!");
}


//
//	Prepara ambiente
//
require("fcs-gerais.php");
require("./include/patrimonio.conf");
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );

if ( $_GET['tipo'] == 'transf' ) {
	mc_dados( "Transfer�ncia de bens patrimoniais" );
	$Texto = "Usu�rio(a),<BR>";
	$Texto = $Texto . "Voc� est� vendo os pedidos de transfer�ncia de bens para sua responsabilidade.<BR>";
	$Texto = $Texto . "Clique sobre a identifica��o do bem e, na pr�xima janela, aceite ou n�o a transfer�ncia.<BR><BR>";
	$Texto = $Texto . "Aceitando, o Agente Patrimonial ir� consolidar a solicita��o e tomar as devidas provid�ncias.<BR>";
} elseif ( $_GET['tipo'] == 'transf-pendente' ) {
	mc_dados( "Transfer�ncia de bens patrimoniais" );
	$Texto = "Agente patrimonial,<BR>";
	$Texto = $Texto . "Voc� est� vendo os pedidos de transfer�ncia de bens ainda n�o conclu�dos.<BR>";
	$Texto = $Texto . "Solicite aos futuros respons�veis que entrem no sistema e completem a transfer�ncia o mais r�pido possivel.<BR>";
} elseif ( $_GET['tipo'] == 'aliena-conf') {
	mc_dados( "Aliena��o/disponibiliza��o de bens patrimoniais" );
	$Texto = "Agente patrimonial,<BR>";
	$Texto = $Texto . "Voc� est� vendo as solicita��es de aliena��o/disponibiliza��o de bens a serem confirmadas.<BR>";
	$Texto = $Texto . "Clique sobre o nome do bem para aceitar a solicita��o.<BR>";
} elseif ( $_GET['tipo'] == 'aliena-fim') {
	mc_dados( "Aliena��o/disponibiliza��o de bens patrimoniais" );
	$Texto = "Agente patrimonial,<BR>";
	$Texto = $Texto . "Voc� est� vendo os bens patrimoniais a serem alienados/disponibilizados.<BR>";
	$Texto = $Texto . "Clique sobre o nome do bem para concluir o processo.<BR>";
}

//
//	Pega os pedidos de transferencia de bens para o usuario
//
if ( $_GET[tipo] == 'transf' ) {
	$qTransf = "SELECT a.*, b.Descricao
			FROM bemtbmodificacao a, bemtbcad b
			WHERE a.MatRespNovo = '$_SESSION[mat]' AND 
				a.NumPat = b.NumPat AND	
				a.Inc = b.Inc AND
				a.Justificativa = 'Transferencia interna' AND
				a.DtTroca = '0000-00-00'
			ORDER BY a.NumPat, a.Inc";

} elseif ( $_GET[tipo] == 'transf-pendente' ) {
	$qTransf = "SELECT a.*, b.Descricao
			FROM bemtbmodificacao a, bemtbcad b
			WHERE a.NumPat = b.NumPat AND	
				a.Inc = b.Inc AND
				a.Justificativa = 'Transferencia interna' AND
				a.DtTroca = '0000-00-00'
			ORDER BY a.DtPedido, a.NumPat, a.Inc";

} elseif ( $_GET[tipo] == 'aliena-conf' ) {
	$qTransf = "SELECT a.*, b.Descricao
			FROM bemtbmodificacao a, bemtbcad b
			WHERE a.NumPat = b.NumPat AND	
				a.Inc = b.Inc AND
				( a.CodSituacaoNovo = 4 OR a.CodSituacaoNovo = 7 ) AND
				a.CodLocalNovo = 0 AND
				a.DtTroca = '0000-00-00'
			ORDER BY a.NumPat, a.Inc";

} elseif ( $_GET[tipo] == 'aliena-fim' ) {
	$qTransf = "SELECT a.*, b.Descricao
			FROM bemtbmodificacao a, bemtbcad b
			WHERE a.NumPat = b.NumPat AND	
				a.Inc = b.Inc AND
				( a.CodSituacaoNovo = 4 OR a.CodSituacaoNovo = 7 ) AND
				a.CodLocalNovo != 0 AND
				a.DtTroca != '0000-00-00'
			ORDER BY a.NumPat, a.Inc";

}
@$rTransf = mysql_query( $qTransf );
if ( !$rTransf ) {
	trata_erro( "N�o foi poss�vel acessar a tabela de bens patrimoniais." );
} elseif ( mysql_num_rows( $rTransf ) == 0 ) {
	echo "<CENTER>";
	if ( $_GET[tipo] == 'transf' ) {
		echo "<B>N�o h� solicita��es de transfer�ncia de bens para voc�.</B>";
	} elseif ( $_GET[tipo] == 'transf-pendente' ) {
		echo "<B>N�o h� pedidos de transfer�ncia de bens pendentes.</B>";
	} elseif ( $_GET[tipo] == 'aliena-conf' ) {
		echo "<B>N�o h� solicita��es de aliena��o/disponibiliza��o de bens.</B>";
	} elseif ( $_GET[tipo] == 'aliena-fim' ) {
		echo "<B>N�o h� bens patrimoniais a serem alienados/disponibilizados.</B>";
	}
	echo "</CENTER>";
	exit;	
}

//
//	Pega nome dos empregados
//
$qFunc = "select $FD_FUNC_MAT, $FD_FUNC_NOME
		from $TB_FUNCIONARIO
		where $V_FUNC_ATIVO
		order by $FD_FUNC_NOME";
@$rFunc = mysql_query( $qFunc );
if ( !$rFunc ) {
	trata_erro( "N�o foi poss�vel acessar a tabela de empregados." );
} elseif ( mysql_num_rows( $rFunc ) == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Aten��o</H3>\n";
	echo "A tabela de empregados est� vazia. Informe ao pessoal da Inform�tica.<BR>\n";
	echo "</CENTER><BR>\n";
	exit;
} elseif ( mysql_num_rows( $rFunc ) >= 0 ) {
	WHILE ( $dFunc = mysql_fetch_array( $rFunc ) ) {
		$Func[$dFunc[$FD_FUNC_MAT]] = $dFunc[$FD_FUNC_NOME];
	}
	mysql_free_result( $rFunc );
}


//
//	Mostrando mensagem e 
//	Preparando o cabecalho da tabela
//
echo $Texto . "<BR>\n";
echo "<CENTER>";
echo "<TABLE BORDER WIDTH=600>";
echo "<TR>";
if ( $_GET[tipo] == 'transf' || $_GET[tipo] == 'aliena-conf' ) {
	echo "<TD WIDTH=58% BGCOLOR='#C0C0C0'><B>Patrim�nio<B></TD>";
	echo "<TD WIDTH=30% BGCOLOR='#C0C0C0'><B>Solicita��o feita por<B></TD>";
	echo "<TD WIDTH=12% BGCOLOR='#C0C0C0'><B>Em<B></TD>";
} elseif ( $_GET[tipo] == 'aliena-fim' ) {
	echo "<TD WIDTH=58% BGCOLOR='#C0C0C0'><B>Patrim�nio a serem alienado/disponibilizado<B></TD>";
	echo "<TD WIDTH=30% BGCOLOR='#C0C0C0'><B>Solicita��o feita por<B></TD>";
	echo "<TD WIDTH=12% BGCOLOR='#C0C0C0'><B>Em<B></TD>";
} elseif ( $_GET[tipo] == 'transf-pendente' ) {
	echo "<TD WIDTH=14% BGCOLOR='#C0C0C0'><B>Patrim�nio<B></TD>";
	echo "<TD WIDTH=37% BGCOLOR='#C0C0C0'><B>De<B></TD>";
	echo "<TD WIDTH=37% BGCOLOR='#C0C0C0'><B>Para<B></TD>";
	echo "<TD WIDTH=12% BGCOLOR='#C0C0C0'><B>Feito em<B></TD>";
}
echo "</TR>";
	
//
//	Apresentando todas as solicitacoes do usuario
//
WHILE ( $Dados = mysql_fetch_array( $rTransf ) ) {
	$bem = $Dados[NumPat] . "-" . $Dados[Inc] . " - " . substr( $Dados[Descricao], 0, 30 );
	echo "<TR>";
	if ( $_GET[tipo] == 'transf' ) {
		$matatual = $Dados[MatRespAtual];
		echo "<TD WIDTH=58%><a href='./bem-transf.php?tipo=aceita&cod=$Dados[CodModifica]'>$bem</a></TD>";
		echo "<TD WIDTH=30%>" . $Func[$matatual] . "</TD>";
		echo "<TD WIDTH=12%>" . troca_data( $Dados[DtPedido] ) . "</TD>";
	} elseif ( $_GET[tipo] == 'transf-pendente' ) {
		$matatual = $Dados[MatRespAtual];
		$matnovo = $Dados[MatRespNovo];
		echo "<TD WIDTH=14%>$Dados[NumPat]-$Dados[Inc]</TD>";
		echo "<TD WIDTH=37%>$Func[$matatual]</TD>";
		echo "<TD WIDTH=37%>$Func[$matnovo]</TD>";
		echo "<TD WIDTH=12%>" . troca_data($Dados[DtPedido]) . "</TD>";
	} elseif ( $_GET[tipo] == 'aliena-conf' ) {
		$matatual = $Dados[MatRespAtual];
		echo "<TD WIDTH=58%><a href='./bem-aliena-conf.php?cod=$Dados[CodModifica]'>$bem</a></TD>";
		echo "<TD WIDTH=30%>$Func[$matatual]</TD>";
		echo "<TD WIDTH=12%>" . troca_data( $Dados[DtPedido] ) . "</TD>";
	} elseif ( $_GET[tipo] == 'aliena-fim' ) {
		$matatual = $Dados[MatRespAtual];
		echo "<TD WIDTH=58%><a href='./bem-aliena-fim.php?cod=$Dados[CodModifica]'>$bem</a></TD>";
		echo "<TD WIDTH=30%>$Func[$matatual]</TD>";
		echo "<TD WIDTH=12%>" . troca_data( $Dados[DtPedido] ) . "</TD>";
	}
	echo "</TR>";
}

echo "</TABLE>";
echo "</CENTER>";

mr_simples();

?>