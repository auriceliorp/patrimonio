<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Transferencia de responsabilidade de bens patrimoniais (Agente)
//	Concluído em 12/04/2004
//	ALTERADO: 15/12/2008
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//
//	ALTERAÇÕES
//	==========
//
//	11/02/2010
//	- Adaptado a exibição dos locais, alterando os itens para PREDIO + LOCAL, conforme acerto nos demais sistemas
//
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
settype($_GET['cod'],"int");

mc_dados( "Transferência de bens patrimoniais - Transferência completa" );

$Texto = "Sr(a). Agente patrimonial,<BR>";
$Texto = $Texto . "Este módulo permite realizar uma transferência completa.<BR>O procedimento se encontra no final do formulário.<BR>";

$Proced = "<HR><H2>Procedimentos</H2>";
$Proced = $Proced . "<BR>Para cancelar a solicitação:<BR>";
$Proced = $Proced . "1) Selecione um bem patrimonial (o local e o responsável atuais serão mostrados);<BR>";
$Proced = $Proced . "2) Escolha o novo responsável e/ou o novo local;<BR>";
$Proced = $Proced . "3) Responda <U>Sim</U> e clique no botão <B>Efetuar transferência</B>";
$Proced = $Proced . "<P>Automaticamente a mudança será realizada. Basta imprimir o(s) novo(s) termo(s).<BR><BR>";
$Proced = $Proced . "Uma mensagem eletrônica será enviada ao responsável atual e ao novo sobre a mudança.<BR>";

//
//	Primeira vez que entra no formulario
//
if ( !$_POST[grava] ) {

	//
	//	Pega o nome dos empregados
	//
	$qFunc = "select $FD_FUNC_MAT, $FD_FUNC_NOME, $FD_FUNC_USERNAME2
			from $TB_FUNCIONARIO
			where $V_FUNC_ATIVO
			order by $FD_FUNC_NOME";

	@$rFunc = mysql_query( $qFunc );
	if ( !$rFunc ) {
		trata_erro( "Não foi possível acessar a tabela de empregados." );
	}

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
	}


	//
	//	Pega os bens patrimoniais da Unidade
	//	1 - ATIVOS
	//	2 - RECEBIDOS EM COMODATO
	//
//	$qEquip = "select $FD_PAT_NUMPAT, $FD_PAT_INC, $FD_PAT_DESC, $FD_PAT_RESP, $FD_PAT_LOCAL
//			from $TB_PATRIMONIO
//			where $FD_PAT_NUMPAT != '0000000' and
//				$FD_PAT_SITUACAO <= $VAR_BemRecebidoEmComodato
//			order by $FD_PAT_NUMPAT, $FD_PAT_INC";
	$qEquip = "select $FD_PAT_NUMPAT, $FD_PAT_INC, $FD_PAT_DESC, $FD_PAT_RESP, $FD_PAT_LOCAL
			from $TB_PATRIMONIO
			where $FD_PAT_NUMPAT != '0000000' and
 				$FD_PAT_SITUACAO IN (1,2,4,7)
			order by $FD_PAT_NUMPAT, $FD_PAT_INC";

	@$rEquip = mysql_query( $qEquip );
	if ( !$rEquip ) {
		trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
	}


//
//	Resposta do Formulario
//
//	ATUALIZACAO dos dados
//
} elseif ( $_POST[grava] ) {

	//
	//	Preparando variaveis
	//
	$DataHoje = date( "Y/m/d" );
	$bem = explode("#" , $_POST[numpat] );
	$usernovo = explode("#" , $_POST[mat] );

	if ( $_POST[resposta] == 'S' ) {
		$qEquip = "update $TB_PATRIMONIO
					set $FD_PAT_LOCAL = '$_POST[local]',
						$FD_PAT_RESP = '$usernovo[0]'
					where $FD_PAT_NUMPAT = '$bem[0]' and $FD_PAT_INC = '$bem[1]'";

		$qTransf = "INSERT INTO bemtbmodificacao
					(MatRespAtual, MatRespNovo, NumPat, Inc, CodLocalAtual, CodLocalNovo, DtPedido, DtTroca, Justificativa)
					VALUES ('$bem[3]', '$usernovo[0]', '$bem[0]', '$bem[1]', '$bem[2]', '$_POST[local]', '$DataHoje', '$DataHoje', 'Transferencia interna' )";

		//
		//	Registrar pendencia de impressao do termo de responsabilidade
		//	Do RESPONSAVEL ATUAL
		//
//		$qTermo = "SELECT * FROM bemtbtermopendente
//				WHERE MatFunc = '$bem[3]' AND Impresso='N'";
//		@$rTermo = mysql_query( $qTermo );
//		if ( mysql_num_rows( $rTermo ) == 0 ) {
//			$qTermo = "INSERT INTO bemtbtermopendente (MatFunc,Impresso) 
//					VALUES ('$bem[3]','N')";
//			mysql_query( $qTermo );
//		}

		//
		//	Registrar pendencia de impressao do termo de responsabilidade
		//	Do NOVO RESPONSAVEL
		//
//		$qTermo = "SELECT * FROM bemtbtermopendente
//				WHERE MatFunc = '$usernovo[0]' AND Impresso='N'";
//		@$rTermo = mysql_query( $qTermo );
//		if ( mysql_num_rows( $rTermo ) == 0 ) {
//			$qTermo = "INSERT INTO bemtbtermopendente (MatFunc,Impresso) 
//					VALUES ('$usernovo[0]','N')";
//			mysql_query( $qTermo );
//		}


		//
		//	Enviando mensagem sobre a transferencia
		//
		$msg = mail(	"$usernovo[2]@embrapa.br",
						"Transferência de responsabilidade/local de bens patrimoniais",
						stripslashes( "Sr(a). $usernovo[1],\n\n" . 
							"O bem listado abaixo foi transferido para sua responsabilidade pelo Agente Patrimonial da Unidade.\n\n" .
							"Você em breve estará recebendo o seu novo termo de responsabilidade que deverá ser assinado.\n\n" . 
							"$bem[0]-$bem[1] - $bem[4]\n\n"  ),
						"From: admin@ctaa.embrapa.br" );
		if ( $msg ) {
			echo "<CENTER>Email enviado com sucesso!</CENTER>";
		} else {
			echo "<CENTER>Email não foi enviado com sucesso!</CENTER>";
		}

	} elseif ( $_POST[resposta] == 'N' ) {
		echo "<CENTER>";
		echo "<H3>Atenção</H3>";
		echo "Nada foi feito pois a transferência foi abandonada pelo agenda patrimonial.";
		echo "</CENTER>";		
		exit;

	} else {
		echo "<CENTER>";
		echo "<H3>Atenção</H3>";
		echo "Você precisa responder <B>Sim</B> ou <B>Não</B>.";
		echo "<FORM ACTION='./bem-transf-agente.php?cod=$_GET[cod]' METHOD='post'>";
		echo "<INPUT TYPE='submit' NAME='submit' VALUE='Voltar'>";
		echo "</FORM>";
		echo "</CENTER>";		
		exit;
	}


	//
	//	Atualiza cadatro de bens patrimoniais
	//
	if ( $_POST[resposta] == 'S' ) {
		if ( $rEquip = mysql_query( $qEquip ) ) {
			echo "<CENTER>";
			echo "<B>Cadastro de bens patrimoniais atualizado com sucesso.</B><BR>\n";
			echo "</CENTER>";
		} else {
			trata_erro( "Não foi possível atualizar o cadastro de bens patrimoniais." );
		}
	}

	@$rTransf = mysql_query( $qTransf );
	if ( !$rTransf ) {
		trata_erro( "Não foi possível atualizar os dados." );
	} else {
		echo "<CENTER>";
		echo "<B>Informação atualizada com sucesso.</B>\n";
		echo "</CENTER>";
	}

	exit;
}	

echo $Texto;
echo "<FORM ACTION='./bem-transf-agente.php?cod=$_GET[cod]' METHOD='post'>";
echo "<CENTER>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Patrimônio: </B></TD>";
echo "<TD><SELECT NAME='numpat' OnChange='MudaLocal(this.form, this.value)'>";
	$um = 0;
	while ( $DadosEquip = mysql_fetch_array( $rEquip ) ) {
		if ( $um == 0 ) {
			$umLocal = $DadosEquip[CodLocal];
			$umResp = $DadosEquip[MatResp];
			$um = 1;
		}
		$bem = $DadosEquip[NumPat] . "-" . $DadosEquip[Inc] . " - " . substr( $DadosEquip[Descricao], 0, 40 );
		echo "<OPTION VALUE='$DadosEquip[NumPat]#$DadosEquip[Inc]#$DadosEquip[CodLocal]#$DadosEquip[MatResp]#$DadosEquip[Descricao]'>$bem</OPTION>";
	}
echo "</SELECT></TD>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>PARA: </B></TD>";
echo "<TD><SELECT NAME='mat'>";
	while ( $DadosFunc = mysql_fetch_array( $rFunc ) ) {
		if ( $DadosFunc[Matricula] == $umResp ) {
			echo "<OPTION SELECTED VALUE= '$DadosFunc[Matricula]#$DadosFunc[Nome]#$DadosFunc[Username2]'>$DadosFunc[Nome]</OPTION>";
		} else {
			echo "<OPTION VALUE= '$DadosFunc[Matricula]#$DadosFunc[Nome]#$DadosFunc[Username2]'>$DadosFunc[Nome]</OPTION>";
		}
	}
echo "</SELECT></TD>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Para onde vai ?</B></TD>";
echo "<TD><SELECT NAME='local'>";
	while ( $DadosLocal = mysql_fetch_array( $rLocal ) ) {
		if ( $DadosLocal[CodLocal] == $umLocal ) {
			echo "<OPTION SELECTED VALUE='$DadosLocal[CodLocal]'>$DadosLocal[DescPredio] - $DadosLocal[DescLocal]</OPTION>";
		} else {
			echo "<OPTION VALUE='$DadosLocal[CodLocal]'>$DadosLocal[DescPredio] - $DadosLocal[DescLocal]</OPTION>";
		}
	}
echo "</SELECT></TD>";
echo "</TR>";

echo "</TABLE>";
echo "<P>";

echo "Realiza esta alteração de local e/ou responsabilidade ? ";
echo "<INPUT TYPE='radio' NAME='resposta' VALUE='S'>Sim";
echo "<INPUT TYPE='radio' NAME='resposta' VALUE='N'>Não";
echo "</P>";

echo "<INPUT TYPE='submit' NAME='grava' VALUE='Efetuar transferência'>";

echo "</CENTER>";
echo "</FORM>";

echo $Proced;

//echo "</BODY>";
mr_simples();

?>


<script language="JavaScript">
function MudaLocal ( form, valor ) {
	string = valor
	string = string.substring(string.indexOf('#')+1,string.length)
	string = string.substring(string.indexOf('#')+1,string.length)
	form.local.value = string.substring(0,string.indexOf('#'))
	string = string.substring(string.indexOf('#')+1,string.length)
	form.mat.value = string.substring(0,string.indexOf('#'))
}
</script>