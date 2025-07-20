<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Relatório de transferências concluídas no período
//	Concluído em 25/08/2003
//	Alterado em 12/04/2004
//	ALTERADO: 15/12/2008
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//


//
//	Verifica se tem algum usuario logado
//
session_start();
if ( !isset($_SESSION[mat]) ) {
	die("Você precisa se logar primeiro!");
}


//
//	Prepara ambiente
//
require("fcs-gerais.php");
require("./include/patrimonio.conf");


//
//	Se esta entrando AGORA
//
if ( !$_POST[montar] ) {

	mc_dados( "Relatório de transferências realizadas no período" );
	//
	//	Monta formulario para pesquisa
	//
	echo "<FORM METHOD='post'>";
	echo "Sr(a). Agente Patrimonial,<BR>\n";
	echo "- Se deixar as <U>duas datas em branco</U>, serão listadas todas as transferências pedidas/concluidas;<BR>\n";
	echo "- Se <U>preencher apenas a data inicial</U>, serão listadas as transferências pedidas/concluídas a partir dela;<BR>\n";
	echo "- Se <U>preencher apenas a data final</U>, serão listadas as transferências pedidas/concluídas até ela;<BR>\n";
	echo "- Se <U>preencher as duas datas</U>, serão listadas as transferências pedidas/concluídas no intervalo.<BR>\n";
	echo "<P><B>Observação:</B> a data consultada na pesquisa é a dia do pedido</P>\n";
	echo "<CENTER><P>";
	echo "<TABLE BORDER>";
	echo "<TR>";
	echo "<TD VALIGN='top' BGCOLOR='#C0C0C0'><B>Data inicial:</B></TD>";
	echo "<TD><INPUT TYPE='text' NAME='datainicio' SIZE=10> Formato: dd/mm/aaaa</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD VALIGN='top' BGCOLOR='#C0C0C0'><B>Data final:</B></TD>";
	echo "<TD><INPUT TYPE='text' NAME='datafim' SIZE=10> Formato: dd/mm/aaaa</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD VALIGN='top' BGCOLOR='#C0C0C0'><B>Tipo de ordenação:</B></TD>";
	echo "<TD><SELECT NAME='ordem'>";
			echo "<OPTION VALUE= '1'>Por patrimônio</OPTION>\n";
			echo "<OPTION VALUE= '2'>Por solicitante</OPTION>\n";
	echo "</SELECT></TD>";
	echo "</TR>";
	echo "</TABLE>";
	echo "</P>";
	echo "<INPUT TYPE='reset' NAME='limpa' VALUE='Limpar formulário'>";
	echo "<INPUT TYPE='submit' NAME='montar' VALUE='Montar relatório'>";
	echo "</CENTER>";
	echo "</FORM>";


//
//	Se eh RESPOSTA do formulario
//	Monta relatório
//
} else {

	mc_simples();

	//
	//	Abre BD suporte para pegar as solicitações de acordo com o tipo de relatorio escolhido
	//
	abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );

	//
	//	Preparando variaveis
	//	Arrumando as datas conforme as entradas:
	//	- Se entrou NENHUMA DATA - lista tudo
	//	- Se entrou APENAS DATA DE INICIO - lista tudo a partir desta data
	//	- Se entrou APENAS DATA FINAL - lista tudo até esta data
	//	- Se entrou AS DUAS DATAS - lista tudo deste intervalo
	//
	if ( $_POST[datainicio] == "" && $_POST[datafim] == "" ) {
		$AteHoje = 1;
		$_POST[datafim] = date("Y/m/d");
	} elseif ( $_POST[datainicio] == "" && $_POST[datafim] != "" ) {
		$AteHoje = 1;
		$_POST[datafim] = troca_data( $_POST[datafim] );
	} elseif ( $_POST[datainicio] != "" && $_POST[datafim] == "" ) {
		$AteHoje = 0;
		$_POST[datainicio] = troca_data( $_POST[datainicio] );
		$_POST[datafim] = date("Y/m/d");
	} elseif ( $_POST[datainicio] != "" && $_POST[datafim] != "" ) {
		$AteHoje = 0;
		$_POST[datainicio] = troca_data( $_POST[datainicio] );
		$_POST[datafim] = troca_data( $_POST[datafim] );
	}

	//
	//	Retira os bens já excluídos
	//
	$excluidos = " AND b.CodLocal != $VAR_Excluido ";

	//
	//	Pesquisa para o relatorio: Transferências realizadas no período
	//
	if ( $AteHoje ) {
		$periodo = "a.DtTroca <= '$_POST[datafim]'";
	} else {
		$periodo = "a.DtTroca >= '$_POST[datainicio]' AND a.DtTroca <= '$_POST[datafim]'";
	}
	if ( $_POST[ordem] == 1 ) {
		$ordenacao = " ORDER BY a.NumPat, a.Inc, a.MatRespAtual, a.DtPedido";
	} else {
		$ordenacao = " ORDER BY a.MatRespAtual, a.NumPat, a.Inc, a.DtPedido";
	}

	$qTransf = "SELECT a.*, b.Descricao
			FROM bemtbmodificacao a, bemtbcad b
			WHERE a.NumPat = b.NumPat AND
				a.DtTroca != '0000-00-00' AND " .
				$periodo .
			$ordenacao;
	@$rTransf = mysql_query( $qTransf );
	$linhas = mysql_num_rows( $rTransf );
	if ( !$rTransf ) {
		trata_erro( "Não foi possível acessar a tabela de solicitações." );
	} elseif ( $linhas == 0 ) {
		echo "<CENTER>";
		echo "<B>Nenhuma transferência foi realizada no período indicado.</B>";
		echo "</CENTER>";
		exit;	
	}

	//
	//	Pegar os nome dos empregados
	//
	$qPesq = "select $FD_FUNC_MAT, $FD_FUNC_NOME
			from $TB_FUNCIONARIO
			where $V_FUNC_ATIVO
			order by $FD_FUNC_NOME";
	@$rPesq = mysql_query( $qPesq );
	WHILE ( $dPesq = mysql_fetch_array( $rPesq ) ) {
		$mat = $dPesq[$FD_FUNC_MAT];
		$Func[$mat] = $dPesq[$FD_FUNC_NOME];
	}
	mysql_free_result ( $rPesq );

	
	//
	//	Pega os locais
	//
	$qLocal = "select a.$FD_PREDIO_DESC, b.$FD_LOCAL_COD, b.$FD_LOCAL_DESC
			from $TB_PREDIO a, $TB_LOCAL b
			where a.$FD_PREDIO_COD = b.$FD_LOCAL_PREDIO
			order by a.$FD_PREDIO_DESC, b.$FD_LOCAL_DESC";
	@$rLocal = mysql_query( $qLocal );
	if ( !$rLocal ) {
		trata_erro( "Não foi possível acessar a tabela de locais." );
	} else {
		while ( $temp2 = mysql_fetch_array( $rLocal ) ) {
			$Local[$temp2[$FD_LOCAL_COD]] = $temp2[$FD_PREDIO_DESC] . " - " . $temp2[$FD_LOCAL_DESC];
		}
	}
	mysql_free_result( $rLocal );

		
	//
	//	IMPRESSAO DO RELATORIO
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

	//
	//	Titulo da tabela
	//
	echo "<CENTER>";
	if ( $_POST[ordem] == 1 ) {
		echo "<H2>Transferências realizadas por patrimônio</H2>\n";
	} elseif ( $_POST[ordem] == 2 ) {
		echo "<H2>Transferências realizadas por solicitante</H2>\n";
	}
	echo "</CENTER>";
	//
	//	Período da consulta
	//
	echo "<P>";
	echo "<B><U>Período da consulta:</U></B> ";
	if ( $AteHoje) {
		echo "até " . troca_data( $_POST[datafim] );
	} else {
		echo "de " . troca_data( $_POST[datainicio] )  . " a " . troca_data( $_POST[datafim] );
	}
	echo "</P>";
	//
	//	Imprime dados
	//
	$chave = "";
	$QtdePorChave = $QtdeTransf = 1;
	WHILE ( $dTransf = mysql_fetch_array( $rTransf ) ) {
		if ( $_POST[ordem] == 1 ) {
			if ( $dTransf[NumPat] != $chave ) {
				if ( $chave != "" ) {
					$QtdePorChave += 1;
					$QtdeTransf += 1;
					echo "</TABLE>";
					echo "</TR>";
					echo "<P><TABLE WIDTH=100%>";
					echo "<TR><TD COLSPAN=2 BGCOLOR='#C0C0C0'><B>Patrimônio:</B> $dTransf[NumPat]-$dTransf[Inc] - $dTransf[Descricao]</TD></TR>";
				} else {
					echo "<P><TABLE WIDTH=100%>";
					echo "<TR><TD COLSPAN=2 BGCOLOR='#C0C0C0'><B>Patrimônio:</B> $dTransf[NumPat]-$dTransf[Inc] - $dTransf[Descricao]</TD></TR>";
				}
			} else {
				$QtdeTransf += 1;
				echo "<TR><TD COLSPAN=2><HR></TD></TR>";
			}

		} elseif ( $_POST[ordem] == 2 ) {
			if ( $dTransf[MatRespAtual] != $chave ) {
				if ( $chave != "" ) {
					$QtdePorChave += 1;
					$QtdeTransf += 1;
					echo "</TABLE>";
					echo "</TR>";
				}
				echo "<P><TABLE WIDTH=100%>";
				echo "<TR><TD COLSPAN=2 BGCOLOR='#C0C0C0'><B>Solicitante:</B> " . $Func[$dTransf[MatRespAtual]] . "</TD></TR>";
			} else {
				$QtdeTransf += 1;
				echo "<TR><TD COLSPAN=2><HR></TD></TR>";
			}
		}

		$mat1 = $dTransf[MatRespAtual];
		$mat2 = $dTransf[MatRespNovo];
		$cod1 = $dTransf[CodLocalAtual];
		$cod2 = $dTransf[CodLocalNovo];
		echo "<TR>";
		if ( $_POST[ordem] == 1 ) {
			echo "<TD WIDTH=50%><B>De:</B> $Func[$mat1]</TD>";
			echo "<TD WIDTH=50%><B>Solicitado em:</B> " . troca_data( $dTransf[DtPedido] ) . "</TD>";
		} elseif ( $_POST[ordem] == 2 ) {
			echo "<TD COLSPAN=2><B>Patrimônio:</B> $dTransf[NumPat]-$dTransf[Inc] - $dTransf[Descricao]</TD>";
		}
		echo "</TR>";
		echo "<TR>";
		echo "<TD WIDTH=50%><B>Para:</B> $Func[$mat2]</TD>";
		echo "<TD WIDTH=50%><B>Aceito em:</B> " . troca_data( $dTransf[DtTroca] ) . "</TD>";
		echo "</TR>";
		if ( $_POST[ordem] == 2 ) {
			echo "<TR>";
			echo "<TD COLSPAN=2><B>Solicitado em:</B> " . troca_data( $dTransf[DtPedido] ) . "</TD>";
			echo "</TR>";
		}
		if ( $dTransf[CodLocalAtual] != $dTransf[CodLocalNovo] ) {
			echo "<TR>";
			echo "<TD WIDTH=50%><B>Local antigo:</B> " . substr( $Local[$dTransf[CodLocalAtual]], 0, 45 ) . "</TD>";
			echo "<TD WIDTH=50%><B>Novo local:</B> " . substr( $Local[$dTransf[CodLocalNovo]], 0, 45 ) . "</TD>";
			echo "</TR>";
		}
		if ( $dTransf[Justificativa] != '' ) {
			echo "<TR><TD COLSPAN=2><B>Obs:</b> $dTransf[Justificativa]</TD></TR>";
		}

		//
		//	Atualizando a chave do relatorio apos impressao da linha
		//
		if ( $_POST[ordem] == 1 ) {
			$chave = $dTransf[NumPat];
		} elseif ( $_POST[ordem] == 2 ) {
			$chave = $dTransf[MatRespAtual];
		}
	}
	echo "<TR><TD COLSPAN=2><HR></TR>";
	echo "</TABLE></P>";
	echo "</CENTER>";


	//
	//	Tabela de resumo
	//
	echo "<CENTER>";
	echo "<P><TABLE WIDTH=260>";
	echo "<TR>";
	echo "<TD COLSPAN=2 BGCOLOR='#C0C0C0'><CENTER><B>RESUMO<B></CENTER></TD>";
	echo "</TR>";
	echo "<TR>";
	if ( $_POST[ordem] == 1 ) {
		echo "<TD WIDTH=90%><B>Qtde bens transferidos no período:<B></TD>";
	} elseif ( $_POST[ordem] == 2 ) {
		echo "<TD WIDTH=90%><B>Qtde de solicitantes no período:<B></TD>";
	}
	echo "<TD WIDTH=10% ALIGN=right>$QtdePorChave</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD WIDTH=90%><B>Qtde de transferências no período:<B></TD>";
	echo "<TD WIDTH=10% ALIGN=right>$QtdeTransf</TD>";
	echo "</TR>";
	echo "<TD COLSPAN=2 BGCOLOR='#C0C0C0'></TD>";
	echo "</TABLE></P>";
	echo "</CENTER>";

}
	
//echo "</BODY>";
mr_simples();

?>
