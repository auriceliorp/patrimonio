<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Alteracao de local (Agente PAtrimonial)
//	Concluído em 11/11/2005
//	ALTERADO: 15/12/208
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

$Texto = "Este módulo permite ao Agente Patrimonial alterar a localização de qualquer bem da Unidade.<BR>O procedimento se encontra no final do formulário.<BR>";

$Proced = "<HR><H2>Procedimentos</H2>";
$Proced = $Proced . "<BR>Para efetuar a modificação:<BR>";
$Proced = $Proced . "1) Selecione um bem patrimonial;<BR>";
$Proced = $Proced . "2) Selecione o novo local;<BR>";
$Proced = $Proced . "3) Clique no botão <B>Alterar local</B><BR><BR>";
$Proced = $Proced . "Automaticamente a mudança será realizada.<BR>";

$Titulo = "Alteração de local - Agente Patrimonial";
mc_dados( $Titulo );

//
//	Primeira vez que entra no formulario
//
if ( !$_POST[grava] ) {

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
	//	Pega os bens patrimoniais ATIVOS (1) ou RECEBIDOS EM COMODATO (2)
	//
	$qEquip = "select *
			from $TB_PATRIMONIO
			where ( $FD_PAT_SITUACAO = $VAR_BemAtivo or $FD_PAT_SITUACAO = $VAR_BemRecebidoEmComodato )
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

	if ( $_POST[resposta] == 'N' ) {
		echo "<CENTER>";
		echo "<B>Operação abandonada pelo usuário.</B>\n";
		echo "</CENTER>";
		exit;

	} elseif ( $_POST[resposta] == '' || $_POST[numpat] == '' || $_POST[local] == '' ) {
		echo "<CENTER>";
		echo "<H3>Atenção</H3>";
		echo "Você precisa selecionar um bem, escolher o novo local e responder <B>Sim</B> ou <B>Não</B>.";
		echo "<FORM ACTION='./bem-local-agente.php' METHOD='post'>";
		echo "<INPUT TYPE='submit' NAME='submit' VALUE='Voltar'>";
		echo "</FORM>";
		echo "</CENTER>";
		exit;
	}

	//
	//	Preparando variaveis
	//
	$DataHoje = date( "Y/m/d" );
	$bem = explode("#" , $_POST[numpat] );
	$texto = addslashes("[" . troca_data($DataHoje) . "] - Troca de local efetuada pelo Agente Patrimonial " . $_SESSION[nome] );

	//
	//	Atualiza cadatro de bens patrimoniais
	//	Se for OK, gravar o registro de transferencia
	//
	$qEquip = "update $TB_PATRIMONIO
				set $FD_PAT_LOCAL = '$_POST[local]'
				where $FD_PAT_NUMPAT = '$bem[0]' and $FD_PAT_INC = '$bem[1]'";

	if ( $rEquip = mysql_query( $qEquip ) ) {
		echo "<CENTER>";
		echo "<B>Cadastro de bens patrimoniais atualizado com sucesso.</B><BR>\n";
		echo "</CENTER>";

		//
		//	Se atualizacao OK, gravar o registro de modificacao
		//
		$qModifica = "INSERT INTO bemtbmodificacao
				( MatRespAtual, MatRespNovo, NumPat, Inc, CodLocalAtual, CodLocalNovo, DtPedido, DtTroca, Justificativa )
				VALUES ( '$_POST[matresp]', '$_POST[matresp]', '$bem[0]', '$bem[1]', '$bem[2]', '$_POST[local]', '$DataHoje', '$DataHoje', '$texto' )";

		@$rModifica = mysql_query( $qModifica );
		if ( !$rModifica ) {
			trata_erro( "Não foi possível gravar o registro da modificacao." );
		} else {
			echo "<CENTER>";
			echo "<B>Registro concluído com sucesso.</B>\n";
			echo "</CENTER>";
		}

	} else {
		trata_erro( "Não foi possível atualizar o cadastro de bens patrimoniais." );

	}
	exit;

}	

echo $Texto;
echo "<FORM ACTION='./bem-local-agente.php' METHOD='post' onSubmit='return verifica( this )'>";

	echo "<INPUT TYPE='hidden' NAME='localatual' VALUE=''>";

	echo "<CENTER><TABLE BORDER WIDTH=100%>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Patrimônio: </B></TD>";
	echo "<TD><SELECT NAME='numpat' OnChange='MudaLocal(this.form, this.value)'>";
		$um = 0;
		while ( $DadosEquip = mysql_fetch_array( $rEquip ) ) {
			if ( $um == 0 ) {
				$umLocal = $DadosEquip[CodLocal];
				$um = 1;
			}
			$bem = $DadosEquip[NumPat] . "-" . $DadosEquip[Inc] . " - " . substr( $DadosEquip[Descricao], 0, 40 );
			echo "<OPTION VALUE= '$DadosEquip[NumPat]#$DadosEquip[Inc]#$DadosEquip[CodLocal]'>$bem</OPTION>";
		}
	echo "</SELECT></TD>";
	echo "</TR>";

	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Para onde vai ?</B></TD>";
	echo "<TD><SELECT NAME='local'>";
		echo "<OPTION SELECTED VALUE= ''>Selecione o novo local</OPTION>";
		while ( $DadosLocal = mysql_fetch_array( $rLocal ) ) {
			if ( $DadosLocal[$FD_LOCAL_COD] == $umLocal ) {
				echo "<OPTION SELECTED VALUE='$DadosLocal[$FD_LOCAL_COD]'>$DadosLocal[$FD_PREDIO_DESC] - $DadosLocal[$FD_LOCAL_DESC]</OPTION>";
			} else {
				echo "<OPTION VALUE='$DadosLocal[$FD_LOCAL_COD]'>$DadosLocal[$FD_PREDIO_DESC] - $DadosLocal[$FD_LOCAL_DESC]</OPTION>";
			}
		}
	echo "</SELECT></TD>";
	echo "</TR>";
	echo "</TABLE>";

	//
	//	Identifica qual serah a pergunta final
	//
	echo "<P>";
	echo "Confirma esta alteração de local ? ";
	echo "<INPUT TYPE='radio' NAME='resposta' VALUE='S'>Sim";
	echo "<INPUT TYPE='radio' NAME='resposta' VALUE='N'>Não";
	echo "</P>";

	echo "<INPUT TYPE='hidden' NAME='matresp' VALUE='" . $DadosEquip[MatResp] . "'>";

	echo "<INPUT TYPE='submit' NAME='grava' VALUE='Alterar local'>";
	echo "</CENTER>";
echo "</FORM>";

echo $Proced;

mr_simples();

?>

<script language="JavaScript">
function MudaLocal ( form, valor ) {
	string = valor
	string = string.substring(string.indexOf('#')+1,string.length)
	string = string.substring(string.indexOf('#')+1,string.length)
	form.local.value = string
	form.localatual.value = string
}
function verifica( form ) {
	if ( form.resposta.selectedIndex == 'S' ) {
		if ( form.localatual.value == form.local.value ) {
			alert ( "O local não foi alterado ou é igual ao anterior. Selecione outro." )
			return false
		}
	}
	return true
}

</script>
