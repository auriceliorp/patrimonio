<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Solicitacao de alienacao/disponibilidade de bens patrimoniais
//	Concluído em 05/04/2004
//	Alterado em 06/04/2008
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

$Titulo = "Solicitação de alienação/disponibilidade de bens patrimoniais - Solicitação";
$Texto = "Usuário(a),<BR>";
$Texto = $Texto . "Este módulo permite solicitar a alienação/disponibilidade de bens patrimoniais.<BR>O procedimento se encontra no final do formulário.<BR>";
$Proced = "<HR><H2>Procedimentos</H2>";
$Proced = $Proced . "<BR>Para recusar/cancelar:<BR>";
$Proced = $Proced . "1) Selecione um bem patrimonial sob sua responsabildade;<BR>";
$Proced = $Proced . "2) Indique se o mesmo vai ser alienado ou colocado em disponibilidade;<BR>";
$Proced = $Proced . "3) Digite a justificativa da solicitação: <B>Motivo e Estado do patrimônio</B>;<BR>";
$Proced = $Proced . "4) Clique no botão <B>Enviar solicitação</B>.<BR><BR>";
$Proced = $Proced . "Uma mensagem eletrônica será enviada ao agente patrimonial solicitando providências.<BR>";
mc_dados( $Titulo );

//
//	Primeira vez que entra no formulario
//
if ( !$_POST['grava'] ) {

	//
	//	Pega os bens patrimoniais do USUARIO
	//
	$qEquip = "SELECT *
			FROM bemtbcad
			WHERE MatResp = '{$_SESSION['mat']}' 
			ORDER BY NumPat, Inc";
	@$rEquip = mysql_query( $qEquip );
	if ( !$rEquip ) {
		trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
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
	//	Verifica se nao tem modificacoes pendentes.
	//	NAO pode haver nenhuma
	//
	$qDuplica = " SELECT DtPedido
				FROM bemtbmodificacao
				WHERE NumPat = '$bem[0]' AND
					Inc = '$bem[1]' AND
					DtTroca = '0000-00-00' ";
	@$rDuplica = mysql_query( $qDuplica );
	if ( !$rDuplica ) {
		trata_erro( "Não foi possível ler a tabela de modificações." );
	} else {
		$Qtde = mysql_num_rows( $rDuplica );
		if ( $Qtde != 0 ) {
			echo "<CENTER><H2>ATENÇÃO</H2>";
			echo "Este bem <B>já possui solicitação pendente</B> para alienação, disponibilização ou transferência";
		} else {
			//
			//	Grava SOLICITACAO
			//
			$qModifica = "INSERT INTO bemtbmodificacao
					(MatRespAtual, NumPat, Inc, CodLocalAtual, DtPedido, CodSituacaoAtual, CodSituacaoNovo, Justificativa)
					VALUES ('$bem[3]', '$bem[0]', '$bem[1]', '$bem[4]', '$DataHoje', '$bem[5]', '{$_POST['tipo']}', '{$_POST['justificativa']}')";
			@$rModifica = mysql_query( $qModifica );
			//echo"bem: ";print_r($bem);
			//echo " INSERT ".$qModifica;
			if ( !$rModifica ) {
				trata_erro( "Não foi possível atualizar os dados." );
			} else {
				echo "<CENTER>";
				echo "<B>Atualização concluída com sucesso.</B>\n";
				echo "</CENTER>";
			}

			//=====================================================
			//
			//	Preparando e enviando mensagem de pedido para todos os Agentes Patrimoniais
			//
			//
			$qAcesso = "select $TB_FUNCIONARIO.$FD_FUNC_USERNAME2
						from $TB_ACESSO, $TB_FUNCIONARIO
						where $TB_ACESSO.MatFunc = $TB_FUNCIONARIO.$FD_FUNC_MAT and
							$TB_ACESSO.TipoUsuario = {$PERFIL_Agente}";
			@$rAcesso = mysql_query( $qAcesso );
			$linhas = mysql_num_rows( $rAcesso );
			if ( !$linhas || $linhas == 0 ) {
				trata_erro( "Não há empregados com perfil de Agente Patrimonial cadastrado. Procure a Informática." );
			} else {
				while ( $Dados = mysql_fetch_array( $rAcesso ) ) {
				/*
					$msg = mail(	"$Dados[$FD_FUNC_USERNAME2]@embrapa.br",
								"Solicitação de alienação/disponibilização de bens",
								stripslashes( "O Sr(a). {$_SESSION['nome']} está solicitando a {$tipo} do bem listado abaixo.\n\n" .
								"$bem[0]-$bem[1] - $bem[2]\n\n" . 
								"<B>Justificativa:</B>\n {$_POST['justificativa']}\n\n" . 
								"Para aceitar/recusar:\n" .
								"1- Na Intranet, vá para a seção Atividades em andamento e clique sobre o nome do patrimônio para abrir o sistema de Bens patrimoniais;\n" .
								"2- No sistema, clique sobre o nome do patrimônio desejado e siga as instruções existente.\n" .
								"Caso existam mais de um bem a processar, não saia da aplicação e repita os passos 1-2."  ),
								"From: admin@ctaa.embrapa.br" );
					if ( $msg ) {
						echo "<CENTER><I>Email</I> enviado com sucesso para o(s) Agente(s) Patrimonial(is)!</CENTER>";
					} else {
						echo "<CENTER>O <I>email</I> ao(s) Agente(s) Patrimonial(is) não foi enviado com sucesso!</CENTER>";
					}
				*/
				}

			}
			//
			//
			//======================================

		}
	}

	exit;
}	

echo $Texto;
echo "<FORM ACTION='./bem-aliena-sol.php?cod={$_GET['cod']}' METHOD='post' onSubmit='return verifica( this )'>";
echo "<CENTER>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Patrimônio: </B></TD>";
echo "<TD><SELECT NAME='numpat'>";
	$um = 0;
	while ( $DadosEquip = mysql_fetch_array( $rEquip ) ) {
		$desc = $DadosEquip['NumPat'] . "-" . $DadosEquip['Inc'] . " - " . substr( $DadosEquip['Descricao'], 0, 40 );
		echo "<OPTION VALUE='{$DadosEquip['NumPat']},{$DadosEquip['Inc']},{$DadosEquip['Descricao']},{$DadosEquip['MatResp']},{$DadosEquip['CodLocal']},{$DadosEquip['CodSituacao']}'>{$desc}</OPTION>";
	}
echo "</SELECT></TD>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Solicitação para: </B></TD>";
echo "<TD><SELECT NAME='tipo'>";
	echo "<OPTION SELECTED VALUE='0'>Selecione uma opção</OPTION>";
	echo "<OPTION VALUE='4'>Alienação</OPTION>";
//	echo "<OPTION VALUE='7'>Disponibilização</OPTION>";
echo "</SELECT></TD>";
echo "</TR>";

echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Justificativa: </B></TD>";
echo "<TD><TEXTAREA ROWS='3' NAME='justificativa' COLS='55'></TEXTAREA></TD>";
echo "</TR>";

echo "</TABLE>";
echo "<P>";

echo "<INPUT TYPE='submit' NAME='grava' VALUE='Enviar solicitação'>";

echo "</CENTER>";
echo "</FORM>";

echo $Proced;

mr_simples();

?>

<script language="JavaScript">
function verifica( form ) {
	if ( form.tipo.value == 0 || form.justificativa.value == '' ) {
		alert ( "O preenchimento de todos os itens é obrigatório. Volte e complete." )
		return false
	}
	return true
}
</script>