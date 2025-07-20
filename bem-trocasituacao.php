<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Troca situacao do bem ( Agente patrimonial)
//	Concluído em 18/08/2009
//	Alterado em 21/12/2009
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
//	Preparando o ambiente
//
require("fcs-gerais.php");
require("./include/patrimonio.conf");
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );

mc_dados( "Troca de situação dos bens patrimoniais" );

$Texto = "Agente patimonial,<BR><BR>";
$Texto = $Texto . "Este módulo permite a você alienar, ceder, doar e transferir um bem patrimonial. Para isto siga o procedimento abaixo.<BR><BR>";

$Proced = "<HR><H2>Procedimentos</H2>";
$Proced = $Proced . "1) Selecione o bem patrimonial<BR>";
$Proced = $Proced . "2) Clique no botão <B>Selecionar o bem</B>.<BR><BR>";
$Proced = $Proced . "Na tela de retorno, você verá informações sobre o bem escolhido, sua situação atual e as opções possíveis para ele. Então:<BR>";
$Proced = $Proced . "3) Verifique a situação do bem;<BR>";
$Proced = $Proced . "4) Selecione a opção de mudança desejada;<BR>";
$Proced = $Proced . "    - Se a opção escolhida for <B>Recido em comodato -> Ativo</B>, digitar o número definitivo do bem ou deixar em brnaco para manter o mesmo.<BR>";
$Proced = $Proced . "5) Clique no botão <B>Trocar a situação</B>.<BR><BR>";
$Proced = $Proced . "Uma mensagem eletrônica será enviada ao responsável atual informando a mudança.<BR><BR>";
$Proced = $Proced . "<U>Atenção:</U> O sistema não faz nenhuma checarem de coerência entre a situação atual e a proposta, sendo da responsabilidade do Agente Patrimonial as consequências desta mudança.<BR>";


//===================================
//
//
//	Entrada no formulario
//
//
if ( !isset($_GET[id]) ) {


	//
	//	Pega os bens patrimoniais 
	//
	$qEquip = "SELECT $FD_PAT_COD, $FD_PAT_NUMPAT, $FD_PAT_INC, $FD_PAT_DESC
			FROM bemtbcad 
			WHERE NumPat != '0000000'
			ORDER BY $FD_PAT_NUMPAT, $FD_PAT_INC";
	@$rEquip = mysql_query( $qEquip );
	if ( !$rEquip ) {
		trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
	}


	//
	//	Tela para seleção
	//
	echo $Texto;

	echo "<FORM ACTION='./bem-trocasituacao.php?id=situacao' METHOD='post' onSubmit='return verifica1( this )'>";
	echo "<CENTER>";
	echo "<P><TABLE BORDER WIDTH=100%>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Patrimônio: </B></TD>";
	echo "<TD><SELECT NAME='bem'>";
		echo "<OPTION SELECTED VALUE=0>Selecione um bem</OPTION>";
		while ( $Dados = mysql_fetch_array( $rEquip ) ) {
			$bem = $Dados[NumPat] . "-" . $Dados[Inc] . " - " . substr( $Dados[Descricao], 0, 40 );
			echo "<OPTION VALUE='$Dados[$FD_PAT_COD]'>$bem</OPTION>";
		}
	echo "</SELECT></TD>";
	echo "</TR>";
	echo "</table></P>";
	
	echo "<INPUT TYPE='button' VALUE='Voltar' ONCLICK='self.history.back();'>";
	echo "<INPUT TYPE='submit' NAME='grava' VALUE='Selecionar o bem'>";

	echo "</CENTER>";
	echo "</FORM>";

	echo $Proced;



//===================================
//
//
//	Resposta do Formulario
//
//	Escolha da situação 
//
//
} elseif ( $_GET[id] == "situacao" ) {


	//
	//	Lendo os dados do bem escolhido
	//
	$qBem = "SELECT a.*, b.$FD_SIT_DESC, c.$FD_FUNC_NOME, c.$FD_FUNC_USERNAME, d.DescLocal
			FROM bemtbcad a LEFT JOIN bemtbsituacao b ON (a.CodSituacao = b.CodPatSituacao)
				LEFT JOIN $TB_FUNCIONARIO c ON (a.MatResp = c.Matricula)
				LEFT JOIN $TB_LOCAL d ON (a.CodLocal = d.CodLocal)
			WHERE a.CodPat = $_POST[bem] ";
	$rBem = mysql_query( $qBem );
	if ( !$rBem ) {
		trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
	} else {
		$temp = mysql_fetch_array( $rBem );
	}


	//
	//	Escolhendo as situações que se adequam à situação atual do bem escolhido.
	//

	//
	//	Se bem for ATIVO (1) ou RECEBIDO EM COMOADTO (2),
	//		Verificar se tem pedido de alienacao/disponibilizacao
	//
	if ( $temp[$FD_PAT_SITUACAO] == $VAR_BemAtivo || $temp[$FD_PAT_SITUACAO] == $VAR_BemRecebidoEmComodato ) {

		$qPesq = "select *
				from $TB_MODIFICACAO
				where NumPat = $temp[NumPat] and
					Inc = $temp[Inc] and
					DtTroca = '0000-00-00' and
					CodSituacaoNovo IN (4,7)";
		$rPesq = mysql_query( $qPesq );
		if ( $rPesq ) {
			if ( mysql_num_rows( $rPesq ) == 1 ) {
				echo "<CENTER><BR><BR><H1>Atenção</H1>";
				echo "A mudança de situação deste bem patrimonial deve ser concluída através do menu de <U>Aceita alienação</U></CENTER>";
				exit;
			}
		}

	//
	//	Se for A SER ALIENADO ou A SER DISPONIBILIZADO
	//		Informar que outra opção do menu deverá ser utilizada
	//
	} elseif ( $temp[$FD_PAT_SITUACAO] == 4 || $temp[$FD_PAT_SITUACAO] == 7 ) {

		echo "<CENTER><BR><BR><H1>Atenção</H1>";
		echo "A mudança de situação deste bem patrimonial deve ser concluída através do menu de <U>Finaliza alienação</U></CENTER>";
		exit;

	}


	//
	//	Mostra os dados do bem selecionado
	//	Apresenta as opções para troca de situacao
	//
	echo $Texto;

	echo "<TABLE BORDER WIDTH='100%'>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH='30%' VALIGN='top'><B>Número patrimonial: </B></TD>";
	echo "<TD WIDTH='70%' VALIGN='top'>$temp[$FD_PAT_NUMPAT] - $temp[$FD_PAT_INC]</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Descrição do bem: </B></TD>";
	echo "<TD WIDTH='70%' VALIGN='top'>$temp[$FD_PAT_DESC]</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH='30%' VALIGN='top'><B>Localização: </B></TD>";
	echo "<TD WIDTH='70%' VALIGN='top'>$temp[$FD_LOCAL_DESC]</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH='30%' VALIGN='top'><B>Responsável: </B></TD>";
	echo "<TD WIDTH='70%' VALIGN='top'>$temp[$FD_FUNC_NOME]</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD WIDTH='30%' VALIGN='top' BGCOLOR='#C0C0C0'><B>Situação de bem:</B></TD>";
	echo "<TD WIDTH='70%' VALIGN='top'>$temp[$FD_SIT_DESC]</TD>";
	echo "</TR>";
	echo "</TABLE>";


	//
	//	Se o bem tiver situacao = "RECEBIDO EM COMODATO" OU situacao = "NAO ENCONTRADO"
	//		Incluir a situacao ATIVO
	//	SENAO
	//		Incluir a situacao RECEBIDO EM COMODATO
	//
	if ( $temp[$FD_PAT_SITUACAO] == $VAR_BemRecebidoEmComodato || $temp[$FD_PAT_SITUACAO] == $VAR_BemNaoEncontrado ) {

		//
		//	Pega e mostra a lista de situações para seleção
		//
		$qSitua = "select *
				from $TB_SITUACAO
				where $FD_SIT_COD IN (1,3,5,6,8,9,11,12)
				order by $FD_SIT_DESC";

	} else {

		//
		//	Pega e mostra a lista de situações para seleção
		//
		$qSitua = "select *
				from $TB_SITUACAO
				where $FD_SIT_COD IN (2,3,5,6,8,9,11,12)
				order by $FD_SIT_DESC";
	}
	@$rSitua = mysql_query( $qSitua );
	if ( !$rSitua ) {
		trata_erro( "Não foi possível acessar a tabela de situações." );
	}


	//
	//	Tela para seleção
	//
	echo "<FORM ACTION='./bem-trocasituacao.php?id=conclusao' METHOD='post' onSubmit='return verifica2( this )'>";
	echo "<CENTER>";

	echo "<P><TABLE BORDER WIDTH='100%'>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH='30%' VALIGN='top'><B>Situação: </B></TD>";
	echo "<TD><SELECT NAME='situacao'>";
		echo "<OPTION SELECTED VALUE=0>Selecione uma situação</OPTION>";
		while ( $Dados = mysql_fetch_array( $rSitua ) ) {
			echo "<OPTION VALUE='$Dados[$FD_SIT_COD]'>$Dados[$FD_SIT_DESC]</OPTION>";
		}
	echo "</SELECT></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH='30%' VALIGN='top'><B>Justificativa: </B></TD>";
	echo "<TD><TEXTAREA ROWS='3' NAME='justifica' COLS='55'></TEXTAREA></TD>";
	echo "</TR>";

	if ( $temp[$FD_PAT_SITUACAO] == $VAR_BemRecebidoEmComodato ) {

		echo "<TR>";
		echo "<TD BGCOLOR='#C0C0C0' WIDTH='30%' VALIGN='top'><B>Número definitivo do bem: </B></TD>";
		echo "<TD><INPUT TYPE='text' NAME='numpatnovo' SIZE='7' MAXLENGTH='7'> - <INPUT TYPE='text' NAME='incnovo' VALUE='000' SIZE='3' MAXLENGTH='3'></TD>";
		echo "</TR>";

	}

	echo "</table></P>";
	
	echo "<INPUT TYPE='hidden' VALUE='$_POST[bem]' NAME='bem'>";
	echo "<INPUT TYPE='hidden' VALUE='$temp[NumPat]' NAME='numpat'>";
	echo "<INPUT TYPE='hidden' VALUE='$temp[Inc]' NAME='inc'>";
	echo "<INPUT TYPE='hidden' VALUE='$temp[CodLocal]' NAME='localatual'>";
	echo "<INPUT TYPE='hidden' VALUE='$temp[CodSituacao]' NAME='situacaoatual'>";
	echo "<INPUT TYPE='hidden' VALUE='$temp[MatResp]' NAME='matrespatual'>";
	echo "<INPUT TYPE='hidden' VALUE='$temp[Obs]' NAME='obsatual'>";

	echo "<INPUT TYPE='button' VALUE='Voltar' ONCLICK='self.history.back();'>";
	echo "<INPUT TYPE='submit' NAME='grava' VALUE='Trocar a situação'>";

	echo "</CENTER>";
	echo "</FORM>";

	echo $Proced;






//===================================
//
//
//	Resposta do Formulario
//
//	FINALIZACAO da troca de situação - GRAVACAO dos dados
//
//
} elseif ( $_GET[id] == "conclusao" ) {


	//
	//	Preparando variaveis
	//
	$DataHoje = date( "Y/m/d" );
	$bem = explode("," , $_POST[numpat] );
	$_POST[justifica] = addslashes( $_POST[justifica] );


	//
	//	Gravando o registro
	//
	//	Se situação nova for ATIVO 
	//
	if ( $_POST[situacao] == 1 ) {
		$_POST[justifica] = $_POST[justifica] . "<BR>O número provisório deste patrimônio era: $_POST[numpat] - $_POST[inc]";
		$_POST[obsatual] = $_POST[obsatual] . "<BR><BR>" . "[" . troca_data( $DataHoje) . "] - " . $_POST[justifica];
		$_POST[justifica] = addslashes( $_POST[justifica] );
		$_POST[obsatual] = addslashes( $_POST[obsatual] );
		$qGravaMod = "INSERT INTO bemtbmodificacao
				(MatRespAtual, NumPat, Inc, CodLocalAtual, CodSituacaoAtual, Justificativa, DtPedido, MatRespNovo, CodSituacaoNovo, DtTroca)
				VALUES ('$_POST[matrespatual]', '$_POST[numpatnovo]', '$_POST[incnovo]', '$_POST[localatual]', '$_POST[situacaoatual]', '$_POST[justifica]', '$DataHoje', '$_POST[matrespatual]', 1, '$DataHoje')";

		//
		//	(vindo de recebido em comodato)
		//
		if ( $_POST[situacaoatual] == 2 ) {
			$qAlteraBem = "UPDATE bemtbcad
					SET CodSituacao = 1,
						NumPat = '$_POST[numpatnovo]',
						Inc = '$_POST[incnovo]',
						Obs = '$_POST[obsatual]'
					WHERE CodPat = '$_POST[bem]' ";
		} else {
			$qAlteraBem = "UPDATE bemtbcad
					SET CodSituacao = 1,
						Obs = '$_POST[obsatual]'
					WHERE CodPat = '$_POST[bem]' ";
		}



	//
	//	Gravando o registro
	//
	//	Se situação nova for RECEBIDO EM COMODATO
	//
	} elseif ( $_POST[situacao] == 2 ) {
		$qGravaMod = "INSERT INTO bemtbmodificacao
				(MatRespAtual, NumPat, Inc, CodLocalAtual, CodSituacaoAtual, Justificativa, DtPedido, MatRespNovo, CodSituacaoNovo, DtTroca)
				VALUES ('$_POST[matrespatual]', '$_POST[numpat]', '$_POST[inc]', '$_POST[localatual]', '$_POST[situacaoatual]', '$_POST[justifica]', '$DataHoje', '$_POST[matrespatual]', 2, '$DataHoje')";
		$qAlteraBem = "UPDATE bemtbcad
				SET CodSituacao = 2
				WHERE CodPat = '$_POST[bem]' ";



	//
	//	Se situação nova for CEDIDO EM COMODATO
	//
	 } elseif ( $_POST[situacao] == 3 ) {
		$qGravaMod = "INSERT INTO bemtbmodificacao
				(MatRespAtual, NumPat, Inc, CodLocalAtual, CodSituacaoAtual, Justificativa, DtPedido, MatRespNovo, CodLocalNovo, CodSituacaoNovo, DtTroca)
				VALUES ('$_POST[matrespatual]', '$_POST[numpat]', '$_POST[inc]', '$_POST[localatual]', '$_POST[situacaoatual]', '$_POST[justifica]', '$DataHoje', '000003', 24, 3, '$DataHoje')";
		$qAlteraBem = "update $TB_PATRIMONIO
				set MatResp = '000003', 
					CodLocal = 24,
					CodSituacao = 3
				where CodPat = '$_POST[bem]' ";



	//
	//	Se situação nova for DOADO
	//
	} elseif ( $_POST[situacao] == 5 ) {
		$qGravaMod = "INSERT INTO bemtbmodificacao
				(MatRespAtual, NumPat, Inc, CodLocalAtual, CodSituacaoAtual, Justificativa, DtPedido, MatRespNovo, CodLocalNovo, CodSituacaoNovo, DtTroca)
				VALUES ('$_POST[matrespatual]', '$_POST[numpat]', '$_POST[inc]', '$_POST[localatual]', '$_POST[situacaoatual]', '$_POST[justifica]', '$DataHoje', '000005', 24, 5, '$DataHoje')";
		$qAlteraBem = "update $TB_PATRIMONIO
				set MatResp = '000005', 
					CodLocal = 24,
					CodSituacao = 5
				where CodPat = '$_POST[bem]' ";

	//
	//	Se situação nova for ALIENADO
	//
	} elseif ( $_POST[situacao] == 6 ) {
		$qGravaMod = "INSERT INTO bemtbmodificacao
				(MatRespAtual, NumPat, Inc, CodLocalAtual, CodSituacaoAtual, Justificativa, DtPedido, MatRespNovo, CodLocalNovo, CodSituacaoNovo, DtTroca)
				VALUES ('$_POST[matrespatual]', '$_POST[numpat]', '$_POST[inc]', '$_POST[localatual]', '$_POST[situacaoatual]', '$_POST[justifica]', '$DataHoje', '000005', 24, 6, '$DataHoje')";
		$qAlteraBem = "update $TB_PATRIMONIO
				set MatResp = '000005', 
					CodLocal = 24,
					CodSituacao = 6
				where CodPat = '$_POST[bem]' ";

	//
	//	Se situação nova for TRANSFERIDO, ROUBADO ou FURTADO
	//
	} elseif ( $_POST[situacao] == 9 || $_POST[situacao] == 8 || $_POST[situacao] == 12) {
		$qGravaMod = "INSERT INTO bemtbmodificacao
				(MatRespAtual, NumPat, Inc, CodLocalAtual, CodSituacaoAtual, Justificativa, DtPedido, MatRespNovo, CodLocalNovo, CodSituacaoNovo, DtTroca)
				VALUES ('$_POST[matrespatual]', '$_POST[numpat]', '$_POST[inc]', '$_POST[localatual]', '$_POST[situacaoatual]', '$_POST[justifica]', '$DataHoje', '000005', 24, '$_POST[situacao]', '$DataHoje')";
		$qAlteraBem = "update $TB_PATRIMONIO
				set MatResp = '000005', 
					CodLocal = 24,
					CodSituacao = '$_POST[situacao]'
				where CodPat = '$_POST[bem]' ";


	//
	//	Se situação nova for NAO ENCONTRADO
	//
	} elseif ( $_POST[situacao] == $VAR_BemNaoEncontrado ) {
		$qGravaMod = "INSERT INTO bemtbmodificacao
				(MatRespAtual, NumPat, Inc, CodLocalAtual, CodSituacaoAtual, Justificativa, DtPedido, CodSituacaoNovo, DtTroca)
				VALUES ('$_POST[matrespatual]', '$_POST[numpat]', '$_POST[inc]', '$_POST[localatual]', '$_POST[situacaoatual]', '$_POST[justifica]', '$DataHoje', $VAR_BemNaoEncontrado, '$DataHoje')";
		$qAlteraBem = "update $TB_PATRIMONIO
				set CodSituacao = $VAR_BemNaoEncontrado
				where CodPat = '$_POST[bem]' ";


	}

	@$rGravaMod = mysql_query( $qGravaMod );
	if ( !$rGravaMod ) {
		trata_erro( "Não foi possível atualizar os dados." );

	} else {
		@$rAlteraBem = mysql_query( $qAlteraBem );
		if ( $rAlteraBem ) {
			echo "<CENTER>";
			echo "<B>Atualização concluída com sucesso.</B>\n";
			echo "</CENTER>";


			//
			//	Se a mudanca de situacao foi de RECEBIDO EM COMODATO para ATIVO
			//		Alterar o Numero provisório de patrimônio nas tabelas: bemtbmodificacao e suptbsolicitacao
			//
			//	OBS: Isto porque é usado os campos NUMPAT-INC como link, ao invés do CODPAT
			//
			if ( $_POST[situacaoatual] == $VAR_BemRecebidoEmComodato && $_POST[situacao] == 1 ) {
				$qAlteraBem2 = "UPDATE producao.bemtbmodificacao
						SET NumPat = '$_POST[numpatnovo]', 
							Inc = '$_POST[incnovo]'
						WHERE NumPat = '$_POST[numpat]' AND Inc = '$_POST[inc]' ";
				@$rAlteraBem2 = mysql_query( $qAlteraBem2 );

				$qAlteraBem3 = "UPDATE producao.suptbsolicitacao
						SET NumPat = '$_POST[numpatnovo]', 
							Inc = '$_POST[incnovo]'
						WHERE NumPat = '$_POST[numpat]' AND Inc = '$_POST[inc]' ";
				@$rAlteraBem3 = mysql_query( $qAlteraBem3 );

			}

		}

	}
	

}	


?>


<script language="JavaScript">

function verifica1( form ) {
	if ( form.bem.value == 0 ) {
		return false
	}
	return true
}


function verifica2( form ) {
	if ( form.situacao.value == 0 || form.justifica.value == '' ) {
		return false
	}

	if ( form.situacaoatual.value == 2 && form.situacao.value == 1 && form.numpatnovo.value == '' ) {
		alert( "Para incorporar um bem ao patrimônio da Embrapa é preciso digitar o número novo." )
		return false
	}

	return true
}

</script>