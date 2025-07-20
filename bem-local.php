<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Alteracao de local
//	Concluído em 08/04/2004
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
//	Preparando o ambiente
//
require("fcs-gerais.php");
require("./include/patrimonio.conf");
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );

$Texto = "Usuário,<BR>";
$Texto = $Texto . "Este módulo permite alterar a localização dos bens sob sua responsabilidade.<BR>O procedimento se encontra no final do formulário.<BR>";

$Proced = "<HR><H2>Procedimentos</H2>";
$Proced = $Proced . "<BR>Para alterar o local do bem patrimonial:<BR>";
$Proced = $Proced . "1) Selecione o bem;<BR>";
$Proced = $Proced . "2) Selecione o novo local;<BR>";
$Proced = $Proced . "3) Clique no botão <B>Alterar local</B>";
$Proced = $Proced . "<P>Automaticamente a mudança será realizada.<BR>";

$Titulo = "Alteração de local";
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
	//	Pega os bens patrimoniais do USUARIO (bem ATIVO ou RECEBIDO EM COMODATO)
	//
	$qEquip = "select *
			from $TB_PATRIMONIO
			where $FD_PAT_RESP  = '$_SESSION[mat]' and
				($FD_PAT_SITUACAO = $VAR_BemAtivo or $FD_PAT_SITUACAO = $VAR_BemRecebidoEmComodato)
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

	} elseif ( $_POST[resposta] == '' ) {
		echo "<CENTER>";
		echo "<H3>Atenção</H3>";
		echo "Você precisa responder <B>Sim</B> ou <B>Não</B>.";
		echo "<FORM ACTION='./bem-local.php' METHOD='post'>";
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
						(MatRespAtual, MatRespNovo, NumPat, Inc, CodLocalAtual, CodLocalNovo, DtPedido, DtTroca, CodSituacaoAtual, CodSituacaoNovo, Justificativa)
						VALUES ('$_SESSION[mat]', '$_SESSION[mat]', '$bem[0]', '$bem[1]', '$bem[2]', '$_POST[local]', '$DataHoje', '$DataHoje', '0', '0', 'Troca de local')";

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
echo "<FORM ACTION='./bem-local.php' METHOD='post' onSubmit='return verifica( this )'>";
echo "<TABLE class='estilo1'>";
echo "<TR>";
echo "<Th class='cinza' WIDTH=30% VALIGN='top'><B>Patrimônio: </B></TD>";
echo "<TD><SELECT NAME='numpat' class='campos'>";
	while ( $DadosEquip = mysql_fetch_array( $rEquip ) ) {
		$bem = $DadosEquip['NumPat'] . "-" . $DadosEquip['Inc'] . " - " . substr( $DadosEquip['Descricao'], 0, 40 );
		echo "<OPTION VALUE= '{$DadosEquip['NumPat']}#{$DadosEquip['Inc']}#{$DadosEquip['CodLocal']}'>{$bem}</OPTION>";
	}
echo "</SELECT></TD>";
echo "</TR>";

echo "<TR>";
echo "<TH class='cinza' WIDTH=30% VALIGN='top'><B>Para onde vai ?</B></TD>";
echo "<TD><SELECT NAME='local' class='campos'>";
	echo "<OPTION SELECTED VALUE=''>Selecione o novo local</OPTION>";
	while ( $DadosLocal = mysql_fetch_array( $rLocal ) ) {
		echo "<OPTION  VALUE='$DadosLocal[$FD_LOCAL_COD]'>$DadosLocal[$FD_PREDIO_DESC] / $DadosLocal[$FD_LOCAL_DESC]</OPTION>";
	}
echo "</SELECT></TD>";
echo "</TR>";
echo "</TABLE>";

echo "<P>";

//
//	Identifica qual serah a pergunta final
//
echo "<P>";
echo "Confirma esta alteração de local ? ";
echo "<INPUT TYPE='radio' NAME='resposta' VALUE='S'>Sim";
echo "<INPUT TYPE='radio' NAME='resposta' VALUE='N'>Não";
echo "</P>";

echo "<INPUT TYPE='submit' NAME='grava' VALUE='Alterar local'>";
echo "</FORM>";

echo $Proced;

//echo "</BODY>";
mr_simples();

?>

<script language="JavaScript">
function verifica( form ) {
	if ( form.local.value == '' ) {
		alert ( "A seleção do novo local é obrigatório. Volte e complete." )
		return false
	}
	return true
}
</script>

