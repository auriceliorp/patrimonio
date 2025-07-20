<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Finalizacao das solicitacoes de alienacao/disponibilidade de bens patrimoniais
//	Conclu�do em 05/04/2004
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//


//
//	Verifica se tem algum usuario logado no momento
//
session_start();
if ( !isset($_SESSION['mat']) ) {
	die("Voce precisa se logar primeiro!");
}

//
//	Preparando o ambiente
//
require("fcs-gerais.php");
require("./include/patrimonio.conf");
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );
settype($_GET['cod'],"int");
mc_dados( "Solicita��o de aliena��o/disponibilidade de bens patrimoniais - Finaliza��o" );

$Proced = "<HR><H2>Procedimentos</H2>";
$Proced = $Proced . "<BR>Para cancelar a solicita��o:<BR>";
$Proced = $Proced . "1) No campo <U>Situa��o final</U> selecione a op��o <B>Cancelar a solicita��o</B>;<BR>";
$Proced = $Proced . "2) Clique no bot�o <B> Finalizar solicita��o</B>.<BR>";
$Proced = $Proced . "<BR>Para finalizar a solicita��o:<BR>";
$Proced = $Proced . "1) No campo <U>Situa��o final</U> selecione a situa��o final do patrim�nio;<BR>";
$Proced = $Proced . "2) No campo <U>Informa��es...</U> digite as informa��es pertinentes ao fechamento da solicita��o;<BR>";
$Proced = $Proced . "3) Responda <B>Sim</B> � pergunta e clique no bot�o <B>Finalizar solicita��o</B>;<BR>";
$Proced = $Proced . "<BR>Para sair sem fazer altera��es:<BR>";
$Proced = $Proced . "1) Responda <B>N�o</B> � pergunta e clique no bot�o <B>Finalizar solicita��o</B>;<BR><BR>";
$Proced = $Proced . "Exemplos de preenchimento do campo <U>Informa��es...</U>";
$Proced = $Proced . "<OL type='a'><LI>Bem alienado em leil�o realizado no dia 14/04/2004 e vendido para XXX Ind. Com de Sucata Ltda. O bem saiu no dia 21/04/2004 com a NF no. 1234/2004. J� foi solicitada a baixa ao DRM.</LI>";
$Proced = $Proced . "<LI>Bem transferido para o CNPS no dia 14/04/2004 com a NF no. 1234/2004. J� foi solicitada a baixa ao DRM.</LI>";
$Proced = $Proced . "<LI>Bem doado/cedido em comodato para a YYY Com. Ltda. e retirado dia 14/04/2004 com a NF no. 1234/2004. J� foi solicitada a baixa ao DRM.</LI></OL>";

//
//	Primeira vez que entra no formulario
//
if ( !$_POST[grava] ) {

	//
	//	Pega o pedido de transfer�ncia em questao
	//
	$qModifica = "SELECT a.*, b.Descricao, b.Obs, c.DescLocal, d.$FD_FUNC_NOME, d.$FD_FUNC_USERNAME, d.$FD_FUNC_MAT
					FROM bemtbmodificacao a, bemtbcad b, $TB_LOCAL c, $TB_FUNCIONARIO d
					WHERE a.NumPat = b.NumPat AND 
						a.Inc = b.Inc AND
						a.CodLocalNovo = c.CodLocal AND
						b.MatResp = d.$FD_FUNC_MAT AND
						a.CodModifica = {$_GET['cod']}";
	@$rModifica = mysql_query( $qModifica );
	if ( !$rModifica ) {
		trata_erro( "N�o foi poss�vel acessar a lista de bens a serem alienados/disponibilizados." );
	} else {
		$Dados = mysql_fetch_array( $rModifica );
		mysql_free_result( $rModifica );
	}


//
//	Resposta do Formulario
//
//	ATUALIZACAO dos dados
//
} elseif ( $_POST['grava'] ) {

	//
	//	Finaliza solicitacao de aliencao/disponibilizacao
	//
	if ( $_POST['resposta'] == 'S' ) {

		$bem = explode("," , $_POST['numpat'] );

		//
		//	Se solicitacao foi CANCELADA
		//
		if ( $_POST['situacao'] == '0' ) {
			$qModifica = "DELETE FROM bemtbmodificacao
							WHERE CodModifica = {$_GET['cod']}";
			@$rModifica = mysql_query( $qModifica );
			if ( !$rModifica ) {
				trata_erro( "N�o foi poss�vel cancelar a solicita��o." );
			} else {
				echo "<CENTER>";
				echo "<B>Solicita��o cancelada com sucesso.</B>\n";
				echo "</CENTER>";
	
				//
				//	Enviando mensagem de NAO aceitacao
				//
				$msg = mail(	"{$bem[4]}@ctaa.embrapa.br",
								"Solicita��o de aliena��o/disponibiliza��o de bens - Cancelamento",
								stripslashes( "O Agente patrimonial da Unidade cancelou a solicita��o de $tipo do bem listado abaixo.\n\n" .
								"{$bem[0]}-{$bem[1]} - {$bem[2]}\n\n" ),
								"From: admin@ctaa.embrapa.br" 
						);
				if ( $msg ) {
					echo "<CENTER>Email ao solicitante enviado com sucesso!</CENTER>";
				} else {
					echo "<CENTER>Email ao solicitante n�o foi enviado com sucesso!</CENTER>";
				}
	
			}

			//
			//	Voltando o bem para ATIVO
			//
			$qEquip = "UPDATE bemtbcad
						SET CodSituacao = 1
						WHERE NumPat = '{$bem[0]}' AND 
							Inc = '{$bem[1]}'";
			@$rEquip = mysql_query( $qEquip );
			if ( $rEquip ) {
				echo "<CENTER>";
				echo "<B>Cadastro de bens patrimoniais atualizado com sucesso.</B><BR>\n";
				echo "</CENTER>";
			} else {
				trata_erro( "N�o foi poss�vel atualizar o cadastro de bens patrimoniais." );
			}

			exit;

		}


		//
		//	Ler as situacoes para gravacao das observacao e justificativa
		//
		$qSituacao = "SELECT * FROM bemtbsituacao";
		@$rSituacao = mysql_query( $qSituacao );
		if ( !$rSituacao ) {
			trata_erro( "N�o foi poss�vel ler a tabela de situa��es." );
		} else {
			WHILE ( $Dados = mysql_fetch_array( $rSituacao ) ) {
				$dSituacao[$Dados['CodPatSituacao']] = $Dados['DescPatSituacao'];
			}
			mysql_free_result( $rSituacao );
		}

		//
		//	Preparando variaveis
		//
		$DataHoje = date( "Y/m/d" );
		$bem = explode("," , $_POST['numpat'] );
		$texto = "[$DataHoje] - " .  $dSituacao[$_POST['situacao']] . "\n{$_POST['final']}";
		if ( $_POST['obs'] == '' ) {
			$_POST['obs'] = addslashes(  $texto . "\nJustificativa: " . $_POST['justificativa'] );
		} else {
			$_POST['obs'] = addslashes(  $_POST['obs'] . "\n\n" . $texto . "\nJustificativa: " . $_POST['justificativa'] );
		}
		$_POST['justificativa'] = addslashes( $_POST['justificativa'] . $texto );
		$tipo = ( $_POST['tipo'] == '4' ? "aliena��o" : "disponiliza��o" );

		//
		//	Atualizando a tabela de modificacoes
		//
		//	Local: $VAR_Excluido (pre-definido em patrimonio.conf)
		//	Responsavel: $VAR_UsuarioAlienacao
		//
		$qModifica = "UPDATE bemtbmodificacao
						SET DtTroca = '$DataHoje',
							CodLocalNovo = {$VAR_Excluido},
							MatRespNovo = '{$VAR_UsuarioAlienacao}',
							CodSituacaoNovo = {$_POST['situacao']},
							Justificativa = '{$_POST['justificativa']}'
						WHERE CodModifica = {$_GET['cod']}";
		@$rModifica = mysql_query( $qModifica );
		if ( !$rModifica ) {
			trata_erro( "N�o foi poss�vel finalizar a solicitacao." );
		} else {
			echo "<CENTER>";
			echo "<B>Tabela de modifica��es atualizada com sucesso.</B>\n";
			echo "</CENTER>";
		}

		//
		//	Atualizando cadastro de patrimonio
		//
		//	Local: $VAR_Excluido (pre-definido em patrimonio.conf)
		//	Responsavel: $VAR_UsuarioAlienacao
		//
		$qEquip = "UPDATE bemtbcad
					SET CodLocal = {$VAR_Excluido},
						MatResp = '{$VAR_UsuarioAlienacao}',
						CodSituacao = {$_POST['situacao']},
						Obs = '{$_POST['obs']}'
					WHERE NumPat = '{$bem[0]}' AND 
						Inc = '{$bem[1]}'";
		@$rEquip = mysql_query( $qEquip );
		if ( $rEquip ) {
			echo "<CENTER>";
			echo "<B>Cadastro de bens patrimoniais atualizado com sucesso.</B><BR>\n";
			echo "</CENTER>";
		} else {
			trata_erro( "N�o foi poss�vel atualizar o cadastro de bens patrimoniais." );
		}

	//
	//	NAO FINALIZA a solicitacao de aliencao/disponibilizacao
	//	Nao faz nada
	//
	} elseif ( $_POST['resposta'] == 'N' ) {

	
	} else {
		echo "<CENTER>";
		echo "<H3>Aten��o</H3>";
		echo "Voc� precisa responder <B>Sim</B> ou <B>N�o</B>.";
		echo "<FORM ACTION='./bem-aliena-fim.php?cod={$_GET['cod']}' METHOD='post'>";
		echo "<INPUT TYPE='submit' NAME='submit' VALUE='Voltar'>";
		echo "</FORM>";
		echo "</CENTER>";
		exit;
	}

	exit;

}	

echo "Agente patimonial,<BR>";
echo "Este m�dulo finaliza a aliena��o/disponibiliza��o de um bem patrimonial mas deve ser executado apenas ap�s a efetiva aliena��o/disponibiliza��o do mesmo.<BR>";
echo "O procedimento se encontra no final do formul�rio.<BR>";

echo "<FORM ACTION='./bem-aliena-fim.php?cod={$_GET['cod']}' METHOD='post' onSubmit='return verifica( this )'>";
echo "<CENTER>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Patrim�nio: </B></TD>";
$bem = 	$Dados['NumPat'] . "-" . $Dados['Inc'] . " - " . substr( $Dados['Descricao'], 0, 40 ) .
"<BR><B>Local atual/tempor�rio:</B> {$Dados['DescLocal']} <BR> <B>Respons�vel:</B> {$Dados[$FD_FUNC_NOME]}";
echo "<TD>{$bem}</TD>";
echo "<INPUT TYPE='hidden' NAME='numpat' VALUE='{$Dados['NumPat']},{$Dados['Inc']},{$Dados['Descricao']},{$Dados[$FD_FUNC_MAT]},{$Dados[$FD_FUNC_USERNAME]}'>";
echo "<INPUT TYPE='hidden' NAME='matresp' VALUE='{$Dados['MatRespAtual']}'>";
echo "<INPUT TYPE='hidden' NAME='justificativa' VALUE='{$Dados['Justificativa']}'>";
echo "<INPUT TYPE='hidden' NAME='obs' VALUE='{$Dados['Obs']}'>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Solicita��o para: </B></TD>";
echo "<TD>". ( $Dados['CodSituacaoNovo'] == '4' ? "Aliena��o" : "Disponibiliza��o" ) . "</TD>";
echo "<INPUT TYPE='hidden' NAME='tipo' VALUE='{$Dados['CodSituacaoNovo']}'>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Justificativa: </B></TD>";
echo "<TD>". stripslashes( $Dados['Justificativa'] ) . "</TD>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Situa��o final: </B></TD>";
echo "<TD><SELECT NAME='situacao' onChange='Situacao(this.form)'>";
	echo "<OPTION SELECTED VALUE=''>Selecione a situa��o</OPTION>";
	echo "<OPTION VALUE='0'>Cancelar a solicita��o</OPTION>";
	echo "<OPTION VALUE='3'>Cedido em comodato</OPTION>";
	echo "<OPTION VALUE='5'>Doado</OPTION>";
	echo "<OPTION VALUE='6'>Alienado</OPTION>";
	echo "<OPTION VALUE='9'>Transferido</OPTION>";
echo "</SELECT></TD>";
echo "</TR>";

echo "<TR>";
echo "<TD COLSPAN=2 BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Informa��es sobre a aliena��o/disponibiliza��o/doa��o/cess�o do patrim�nio: </B><BR>
(Ver exemplos no final desta p�gina)</TD>";
echo "</TR>";
echo "<TR>";
echo "<TD COLSPAN=2><TEXTAREA ROWS='2' NAME='final' COLS='70'></TEXTAREA></TD>";
echo "</TR>";

echo "</TABLE>";

echo "Finaliza esta solicita��o ? ";
echo "<INPUT TYPE='radio' NAME='resposta' VALUE='S'>Sim";
echo "<INPUT TYPE='radio' NAME='resposta' VALUE='N'>N�o";

echo "<P>";
echo "<INPUT TYPE='button' VALUE='Voltar' ONCLICK='self.history.back();'>";
echo "<INPUT TYPE='submit' NAME='grava' VALUE='Finalizar solicita��o'>";
echo "</P>";

echo "</CENTER>";
echo "</FORM>";

echo $Proced;

mr_simples();


?>


<script language="JavaScript">
function Situacao( form ) {
	if ( form.situacao.value == '0' ) {
		form.final.value = 'Cancelada'
	} else {
		form.final.value = ''
	}
}

function verifica( form ) {
	if ( form.situacao.value == '' || form.final.value == '' ) {
		alert ( "O preenchimento de todos os itens � obrigat�rio. Volte e complete." )
		return false
	}
	return true
}
</script>