<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Confirmacao da solicitacao de alienacao/disponibilidade de bens patrimoniais
//	Concluído em 05/04/2004
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
mc_dados( "Solicitação de alienação/disponibilidade de bens patrimoniais - Confirmação" );

$Proced = "<HR><H2>Procedimentos</H2>";
$Proced = $Proced . "<BR>Para recusar/cancelar:<BR>";
$Proced = $Proced . "1) Responda <U>Não</U> e clique no botão <B> Confirmar/recusar solicitação</B>.<BR>";
$Proced = $Proced . "<BR>Para confirmar:<BR>";
$Proced = $Proced . "1) Recolha o bem para um local temporário, se for o caso;<BR>";
$Proced = $Proced . "2) Selecione na lista o local onde você o colocou;<BR>";
$Proced = $Proced . "3) Responda <U>Sim</U> e clique no botão <B>Confirmar/recusar solicitação</B>;<BR>";
$Proced = $Proced . "4) O bem será colocado sob a responsabilidade do Agente Patrimonial e a situação dele passará a ser <B>A ser alienado</B>.<BR>";
$Proced = $Proced . "<BR>Uma mensagem eletrônica será enviada ao solicitante informando esta confirmação.<BR>";

//
//	Primeira vez que entra no formulario
//
if ( !$_POST['grava'] ) {

	//
	//	Pega o pedido de transferência em questao
	//
	$qModifica = "SELECT a.*, b.Descricao, c.DescLocal, d.$FD_FUNC_NOME, d.$FD_FUNC_MAT, d.$FD_FUNC_USERNAME
			FROM bemtbmodificacao a, bemtbcad b, $TB_LOCAL c, $TB_FUNCIONARIO d
			WHERE a.NumPat = b.NumPat AND 
				a.Inc = b.Inc AND
				b.MatResp = d.$FD_FUNC_MAT AND
				b.CodLocal = c.CodLocal AND
				a.CodModifica = {$_GET['cod']}";
	@$rModifica = mysql_query( $qModifica );
	if ( !$rModifica ) {
		trata_erro( "Não foi possível acessar a tabela de pedidos de alienação/disponibilidade." );
	} else {
		$Dados = mysql_fetch_array( $rModifica );
		mysql_free_result( $rModifica );
	}


	//
	//	Pega os locais
	//
	$qLocal = "select $FD_LOCAL_COD, $FD_LOCAL_DESC
			from $TB_LOCAL
			order by $FD_LOCAL_DESC";
	@$rLocal = mysql_query( $qLocal );
	if ( !$rLocal ) {
		trata_erro( "Não foi possível acessar a tabela de locais." );
	}


//
//	Resposta do Formulario
//
//	ATUALIZACAO dos dados
//
} elseif ( $_POST['grava'] ) {

	//
	//	Preparando variaveis
	//
	$DataHoje = date( "Y/m/d" );
	$bem = explode("," , $_POST['numpat'] );
	$_POST['Justificativa'] = addslashes( $_POST['justificativa'] );
	$tipo = ( $_POST['tipo'] == '4' ? "alienação" : "disponilização" );

	//
	//	Aceita solicitacao de aliencao/disponibilizacao
	//
	if ( $_POST['resposta'] == 'S' ) {
		$qModifica = "UPDATE bemtbmodificacao
				SET DtTroca = '{$DataHoje}',
					CodLocalNovo = {$_POST['local']},
					MatRespNovo = '{$_SESSION['mat']}'
				WHERE CodModifica = {$_GET['cod']}";
		@$rModifica = mysql_query( $qModifica );
		if ( !$rModifica ) {
			trata_erro( "Não foi possível cadastrar a confirmação." );
		} else {
			echo "<CENTER>";
			echo "<B>Confirmação atualizada com sucesso.</B>\n";
			echo "</CENTER>";
		}

		//
		//	Atualizando cadastro de patrimonio
		//
		$qEquip = "UPDATE bemtbcad
				SET CodLocal = '$_POST[local]',
					MatResp = '$_SESSION[mat]',
					CodSituacao = '$_POST[tipo]'
				WHERE NumPat = '$bem[0]' AND 
					Inc = '$bem[1]'";
		@$rEquip = mysql_query( $qEquip );
		if ( $rEquip ) {
			echo "<CENTER>";
			echo "<B>Cadastro de bens patrimoniais atualizado com sucesso.</B><BR>\n";
			echo "</CENTER>";
		} else {
			trata_erro( "Não foi possível atualizar o cadastro de bens patrimoniais." );
		}

		//
		//	Enviando mensagem de aceitacao
		//
		$msg = mail(	"$bem[4]@ctaa.embrapa.br",
				"Solicitação de alienação/disponibilização de bens - Aceitação",
				stripslashes( "O Agente patrimonial da Unidade aceitou o pedido de $tipo do bem listado abaixo.\n\n" .
				"$bem[0]-$bem[1] - $bem[2]\n\n" ), 
				"From: admin@ctaa.embrapa.br" 
			);
		if ( $msg ) {
			echo "<CENTER>Email ao solicitante enviado com sucesso!</CENTER>";
		} else {
			echo "<CENTER>Email ao solicitante não pode ser enviado!</CENTER>";
		}

	//
	//	NAO Aceita o pedido de aliencao/disponibilizacao
	//
	} elseif ( $_POST[resposta] == 'N' ) {
		$qModifica = "DELETE FROM bemtbmodificacao
				WHERE CodModifica = '$_GET[cod]'";
		@$rModifica = mysql_query( $qModifica );
		if ( !$rModifica ) {
			trata_erro( "Não foi possível excluir a solicitação." );
		} else {
			echo "<CENTER>";
			echo "<B>Solicitação excluída com sucesso!</B>\n";
			echo "</CENTER>";
		}

		//
		//	Enviando mensagem de NAO aceitacao
		//
		$msg = mail(	"$bem[4]@ctaa.embrapa.br",
				"Solicitação de alienação/disponibilização de bens",
				stripslashes( "O Agente patrimonial da Unidade NÃO aceitou o pedido de $tipo do bem listado abaixo. Com isto, o pedido foi cancelado.\n\n" .
				"$bem[0]-$bem[1] - $bem[2]\n\n" ),
				"From: admin@ctaa.embrapa.br" 
			);
		if ( $msg ) {
			echo "<CENTER>Email ao solicitante enviado com sucesso!</CENTER>";
		} else {
			echo "<CENTER>Email ao solicitante não pode ser enviado!</CENTER>";
		}

	} else {
		echo "<CENTER>";
		echo "<H3>Atenção</H3>";
		echo "Você precisa responder <B>Sim</B> ou <B>Não</B>.";
		echo "<FORM ACTION='./bem-aliena-conf.php?cod=$_GET[cod]' METHOD='post'>";
		echo "<INPUT TYPE='submit' NAME='submit' VALUE='Voltar'>";
		echo "</FORM>";
		echo "</CENTER>";
		exit;
	}

	exit;
}	

echo "Agente patimonial,<BR>";
echo "Este módulo deverá ser usado para aceitar uma solicitação de alienação/disponibilização. O bem deve ser recolhido, saindo da responsabilidade do solicitante.<BR>";
echo "O procedimento se encontra no final do formulário<BR>";

echo "<FORM ACTION='./bem-aliena-conf.php?cod=$_GET[cod]' METHOD='post' onSubmit='return verifica( this )'>";
echo "<CENTER>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Patrimônio: </B></TD>";
$desc = 	$Dados[NumPat] . "-" . $Dados[Inc] . " - " . substr( $Dados[Descricao], 0, 40 ) .
"<BR><B>Local:</B> $Dados[DescLocal] <BR> <B>Responsável:</B> $Dados[Nome]";
echo "<TD>$desc</TD>";
echo "<INPUT TYPE='hidden' NAME='numpat' VALUE='$Dados[NumPat],$Dados[Inc],$Dados[Descricao],$Dados[Matricula],$Dados[Username]'>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Solicitação para: </B></TD>";
echo "<TD>". ( $Dados[CodSituacaoNovo] == '4' ? "Alienação" : "Disponibilização" ) . "</TD>";
echo "<INPUT TYPE='hidden' NAME='tipo' VALUE='$Dados[CodSituacaoNovo]'>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Justificativa: </B></TD>";
echo "<TD>". stripslashes( $Dados[Justificativa] ) . "</TD>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Recolhido temporariamente para: </B></TD>";
echo "<TD><SELECT NAME='local'>";
	echo "<OPTION SELECTED VALUE='0'>Selecione o local</OPTION>";
	while ( $DadosLocal = mysql_fetch_array( $rLocal ) ) {
		echo "<OPTION VALUE='$DadosLocal[CodLocal]'>$DadosLocal[DescLocal]</OPTION>";
	}
echo "</SELECT></TD>";
echo "</TR>";

echo "</TABLE>";
echo "<P>";

echo "<P>";
echo "Aceita a solicitação de alienação/disponibilização deste bem ? ";
echo "<INPUT TYPE='radio' NAME='resposta' VALUE='S'>Sim";
echo "<INPUT TYPE='radio' NAME='resposta' VALUE='N'>Não";
echo "</P>";

echo "<P>";
echo "<INPUT TYPE='button' VALUE='Voltar' ONCLICK='self.history.back();'>";
echo "<INPUT TYPE='submit' NAME='grava' VALUE='Confirmar/recusar solicitação'>";
echo "</P>";

echo "</CENTER>";
echo "</FORM>";

echo $Proced;

//echo "</BODY>";
mr_simples();

?>

<script language="JavaScript">
function verifica( form ) {
	if ( form.local.value == 0 || form.tipo.value == 0 || form.justificativa.value == '' ) {
		alert ( "O preenchimento de todos os itens é obrigatório. Volte e complete." )
		return false
	}
	return true
}
</script>