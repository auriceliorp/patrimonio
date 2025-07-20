<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Transferencia de responsabilidade de bens patrimoniais (Empregado)
//	Conclu�do em 12/04/2004
//	ALTERADO: 15/12/2008
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//
//	19/04/2011
//
//	Permitir que o empregado possa solicitar transfer�ncia de bens que est�o para disponibiliza��o e/ou aliena��o
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

if ( $_GET[tipo] == 'pede' ) {
	$Titulo = "Transfer�ncia de bens patrimoniais - Solicita��o";
	$Texto = "Usu�rio(a),<BR>";
	$Texto = $Texto . "Este m�dulo permite solicitar a transfer�ncia de bens para outro empregado.<BR>O procedimento se encontra no final do formul�rio.<BR>";
	$Proced = "<HR><H2>Procedimentos</H2>";
	$Proced = $Proced . "Para solicitar a transfer�ncia de um bem patrimonial:<BR>";
	$Proced = $Proced . "1) Selecione um bem patrimonial sob sua responsabildade;<BR>";
	$Proced = $Proced . "2) Selecione o empregado para o qual voc� pretende transferir o bem;<BR>";
	$Proced = $Proced . "3) Clique no bot�o <B>Enviar solicita��o</B>.<BR><BR>";
	$Proced = $Proced . "Uma mensagem eletr�nica ser� enviada ao empregado selecionado solicitando a aceita��o.<BR><BR>";
	$Proced = $Proced . "<U>Importante:</U> A transfer�ncia deve ser negociada previamente para evitar problemas futuros.";
} elseif ( $_GET[tipo] == 'aceita' ) {
	$Titulo = "Transfer�ncia de bens patrimoniais - Aceita��o";
	$Texto = "Usu�rio(a),<BR>";
	$Texto = $Texto . "Este m�dulo permite aceitar a transfer�ncia de um bem patrimonial para sua responsabilidade.<BR>O procedimento se encontra no final do formul�rio.<BR>";
	$Proced = "<HR><H2>Procedimentos</H2>";
	$Proced = $Proced . "Para recusar a solicita��o:<BR>";
	$Proced = $Proced . "1) Responda <U>N�o</U> e clique no bot�o <B> Aceitar/recusar transfer�ncia</B>.<BR><BR>";
	$Proced = $Proced . "Para aceitar a solicita��o:<BR>";
	$Proced = $Proced . "1) Selecione o novo local deste bem, se for o caso;<BR>";
	$Proced = $Proced . "2) Responda <U>Sim</U> e clique no bot�o <B> Aceitar/recusar transfer�ncia</B>.<BR><BR>";
	$Proced = $Proced . "Uma mensagem eletr�nica ser� enviada ao solicitante informando a decis�o.<BR><BR>";
	$Proced = $Proced . "<U>Importante:</U> A transfer�ncia ser� efetuada automaticamente.";
}
mc_dados( $Titulo );


//
//	Primeira vez que entra no formulario
//
if ( !$_POST[grava] ) {

	//
	//	Pega o nome dos empregados
	//
	$qFunc = "select $FD_FUNC_MAT, $FD_FUNC_NOME, $FD_FUNC_USERNAME
			from $TB_FUNCIONARIO
			where $V_FUNC_ATIVO
			order by $FD_FUNC_NOME";

	@$rFunc = mysql_query( $qFunc );
	if ( !$rFunc ) {
		trata_erro( "N�o foi poss�vel acessar a tabela de empregados." );
	}

	if ( $_GET[tipo] == 'aceita' ) {
		WHILE ( $dFunc = mysql_fetch_array( $rFunc ) ) {
			$mat = $dFunc[$FD_FUNC_MAT];
			$Func[$mat] = $dFunc[$FD_FUNC_NOME];
			$User[$mat] = $dFunc[$FD_FUNC_USERNAME];
		}
		mysql_free_result( $rFunc );
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
			trata_erro( "N�o foi poss�vel acessar a tabela de locais." );
//		} else {
//			while ( $temp2 = mysql_fetch_array( $rLocal ) ) {
//				$Local[$temp2[$FD_LOCAL_COD]] = $temp2[$FD_PREDIO_DESC] . " - " . $temp2[$FD_LOCAL_DESC];
//			}
		}
		//mysql_free_result( $rLocal );

/*	//
	//	Pega os locais
	//
	$qLocal = "select $FD_LOCAL_COD, $FD_LOCAL_DESC	
			from $TB_LOCAL
			where $FD_LOCAL_TIPO = 'G'
			order by $FD_LOCAL_DESC";

	@$rLocal = mysql_query( $qLocal );
	if ( !$rLocal ) {
		trata_erro( "N�o foi poss�vel acessar a tabela de locais." );
	}
*/

	//
	//	Se for PEDIDO
	//
	if ( $_GET[tipo] == 'pede' ) { 
		//
		//	Pega os bens patrimoniais do USUARIO
		//	1 - ATIVO
		//	2 - RECEBIDO EM COMODATO
		//
		$qEquip = "select *
				from $TB_PATRIMONIO
				where $FD_PAT_RESP = '$_SESSION[mat]' and
					$FD_PAT_SITUACAO <= $VAR_BemRecebidoEmComodato
				order by $FD_PAT_NUMPAT, $FD_PAT_INC";
		@$rEquip = mysql_query( $qEquip );
		if ( !$rEquip ) {
			trata_erro( "N�o foi poss�vel acessar a tabela de bens patrimoniais." );
		}

	//
	//	Se for ACEITE
	//
	} elseif ( $_GET[tipo] == 'aceita' ) {
		//
		//	Pega o pedido de transfer�ncia em questao
		//
		$qTransf = "SELECT a.*, b.$FD_PAT_DESC, b.$FD_PAT_LOCAL
				FROM bemtbmodificacao a, $TB_PATRIMONIO b
				WHERE a.NumPat = b.$FD_PAT_NUMPAT and
					a.Inc = b.$FD_PAT_INC and
					a.CodModifica = '$_GET[cod]'";

		@$rTransf = mysql_query( $qTransf );
		if ( !$rTransf ) {
			trata_erro( "N�o foi poss�vel acessar a tabela de pedidos de transfer�ncia." );
		} else {
			$DadosTransf = mysql_fetch_array( $rTransf );
			mysql_free_result( $rTransf );
		}

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

	//
	//	Gravando o registro
	//
	if ( $_GET[tipo] == 'pede' ) {

		//
		//	Verifica se ja tem pedidos de transferencia para este bem.
		//	NAO pode haver nenhum
		//
		$qDuplica = "SELECT DtPedido
				FROM bemtbmodificacao
				WHERE NumPat = '$bem[0]' AND
					Inc = '$bem[1]' AND
					DtTroca = '0000-00-00' ";

		@$rDuplica = mysql_query( $qDuplica );
		if ( !$rDuplica ) {
			trata_erro( "N�o foi poss�vel ler a tabela de pend�ncias." );
		} else {
			$Qtde = mysql_num_rows( $rDuplica );
			if ( $Qtde != 0 ) {
				echo "<CENTER><H2>ATEN��O</H2>";
				echo "Este bem <B>j� possui solicita��o pendente</B> de transfer�ncia";
				exit;

			} else {
				$qTransf = "INSERT INTO bemtbmodificacao
						(MatRespAtual, MatRespNovo, NumPat, Inc, CodLocalAtual, DtPedido, Justificativa)
						VALUES ('$_SESSION[mat]', '$usernovo[0]', '$bem[0]', '$bem[1]', '$bem[2]', '$DataHoje', 'Transferencia interna' )";
				//
				//	Enviando mensagem de pedido
				//
				$msg = mail( "$usernovo[1]@ctaa.embrapa.br",
						"Solicita��o de transfer�ncia de bens",
						"O Sr(a). $_SESSION[nome] est� solicitando a transfer�ncia do bem abaixo para sua responsabilidade.\n\n" .
							"$bem[0]-$bem[1] - $bem[4]\n\n" . 
						"Para aceitar/recusar:\n" .
						"1- Na Intranet, v� para a se��o Atividades em andamento e clique sobre o nome do patrim�nio para abrir o sistema de Controle de bens patrimoniais;\n" .
						"2- No sistema, clique sobre o nome do patrim�nio desejado e siga as instru��es existente.\n" .
						"Caso existam mais de um bem a receber, n�o saia da aplica��o e repita os passos 1-2.",
						"From: Controle de bens patrimoniais <admin@ctaa.embrapa.br>" );
				if ( $msg ) {
					echo "<CENTER>Email enviado com sucesso!</CENTER>";
				} else {
					echo "<CENTER>Email n�o foi enviado com sucesso!</CENTER>";
				}
			}
		}

	} elseif ( $_GET[tipo] == 'aceita' ) {
		if ( $_POST[resposta] == 'S' ) {
			$qTransf = "UPDATE bemtbmodificacao
					SET DtTroca = '$DataHoje',
						CodLocalNovo = '$_POST[local]'
					WHERE CodModifica = '$_GET[cod]' ";

			$qEquip = "update $TB_PATRIMONIO
					set $FD_PAT_LOCAL = '$_POST[local]',
						$FD_PAT_RESP = '$_SESSION[mat]'
					where $FD_PAT_NUMPAT = '$bem[0]' and $FD_PAT_INC = '$bem[1]' ";

			//
			//	Registrar pendencia do termo de responsabilidade
			//	Do RESPONSAVEL ATUAL
			//
//			$qTermo = "SELECT * FROM bemtbtermopendente
//					WHERE MatFunc = '$_POST[matatual]' AND 
//						Impresso='N'";
//			@$rTermo = mysql_query( $qTermo );
//			if ( mysql_num_rows( $rTermo ) == 0 ) {
//				$qTermo = "INSERT INTO bemtbtermopendente (MatFunc,Impresso) 
//						VALUES ('$_POST[matatual]','N')";
//				mysql_query( $qTermo );
//			}

			//
			//	Registrar pendencia do termo de responsabilidade
			//	Do NOVO RESPONSAVEL
			//
//			$qTermo = "SELECT * FROM bemtbtermopendente
//					WHERE MatFunc = '$_SESSION[mat]' AND 
//						Impresso='N'";
//			@$rTermo = mysql_query( $qTermo );
//			if ( mysql_num_rows( $rTermo ) == 0 ) {
//				$qTermo = "INSERT INTO bemtbtermopendente (MatFunc,Impresso) 
//						VALUES ('$_SESSION[mat]','N')";
//				mysql_query( $qTermo );
//			}

			//
			//	Enviando mensagem de aceite
			//
			$msg = mail(	"$_POST[useratual]@ctaa.embrapa.br",
							"Aceite de transfer�ncia de bens",
							"$_SESSION[nome] aceitou a transfer�ncia do bem abaixo.\n\n" .
							"$bem[0]-$bem[1] - $bem[4]\n\n",
							"From: admin@ctaa.embrapa.br" );
			if ( $msg ) {
				echo "<CENTER>Email enviado com sucesso!</CENTER>";
			} else {
				echo "<CENTER>Email n�o foi enviado com sucesso!</CENTER>";
			}


		} elseif ( $_POST[resposta] == 'N' ) {
			$qTransf = "DELETE FROM bemtbmodificacao
					WHERE CodModifica = '$_GET[cod]'";

			//
			//	Enviando mensagem de recusa
			//
			$msg = mail(	"$_POST[useratual]@ctaa.embrapa.br",
							"Recusa de transfer�ncia de bens",
							"$_SESSION[nome] n�o aceitou a transfer�ncia do bem abaixo.\n\n" .
							"$bem[0]-$bem[1] - $bem[4]\n\n",
							"From: admin@ctaa.embrapa.br" );
			if ( $msg ) {
				echo "<CENTER>Email enviado com sucesso!</CENTER>";
			} else {
				echo "<CENTER>Email n�o foi enviado com sucesso!</CENTER>";
			}

		} else {
			echo "<CENTER>";
			echo "<H3>Aten��o</H3>";
			echo "Voc� precisa responder <B>Sim</B> ou <B>N�o</B>.";
			echo "<FORM ACTION='./bem-transf.php?tipo=$_GET[tipo]&cod=$_GET[cod]' METHOD='post'>";
			echo "<INPUT TYPE='submit' NAME='submit' VALUE='Voltar'>";
			echo "</FORM>";
			echo "</CENTER>";
			exit;
		}

	}


	//
	//	Se for tipo = ACEITAR
	//	Atualiza cadatro de bens patrimoniais
	//
	if ( $_POST[resposta] == 'S' ) {
		if ( $_GET[tipo] == 'aceita' ) {
			if ( $rEquip = mysql_query( $qEquip ) ) {
				echo "<CENTER>";
				echo "<B>Cadastro de bens patrimoniais atualizado com sucesso.</B><BR>\n";
				echo "</CENTER>";
			} else {
				trata_erro( "N�o foi poss�vel atualizar o cadastro de bens patrimoniais." );
			}
		}
	}

	@$rTransf = mysql_query( $qTransf );
	if ( !$rTransf ) {
		trata_erro( "N�o foi poss�vel atualizar os dados." );
	} else {
		echo "<CENTER>";
		if ( $_GET[tipo] == 'pede' ) {
			echo "<B>Pedido cadastrado com sucesso!</B>\n";
		} elseif ( $_GET[tipo] == 'aceita' ) {
			echo "<B>Pedido conclu�do com sucesso!</B>\n";
			echo "<FORM ACTION='bem-pesquisa.php?tipo=transf' METHOD='post'>";
			echo "<INPUT TYPE='submit' NAME='voltar' VALUE='voltar'>";
			echo "</FORM>";
		}
		echo "</CENTER>";
	}
	exit;
}	

//============================
//
//	Tela de entrada dos dados
//
//
//
echo $Texto;
echo "<FORM ACTION='./bem-transf.php?tipo=$_GET[tipo]&cod=$_GET[cod]' METHOD='post' onSubmit='return verifica( this )'>";
echo "<CENTER>";
echo "<TABLE BORDER WIDTH=100%>";
echo "<TR>";
echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Patrim�nio: </B></TD>";

if ( $_GET[tipo] == 'pede' ) {
	echo "<INPUT TYPE='hidden' NAME='tipo' VALUE='pede'>";

	echo "<TD><SELECT NAME='numpat' OnChange='MudaLocal(this.form, this.value)'>";
		$um = 0;
		echo "<OPTION SELECTED VALUE=''>Selecione o bem a ser transferido</OPTION>";
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
		echo "<OPTION SELECTED VALUE=''>Selecione o novo respons�vel</OPTION>";
		while ( $DadosFunc = mysql_fetch_array( $rFunc ) ) {
			echo "<OPTION VALUE= '$DadosFunc[Matricula]#$DadosFunc[Username]'>$DadosFunc[Nome]</OPTION>";
		}
	echo "</SELECT></TD>";
	echo "</TR>";

} elseif ( $_GET[tipo] == 'aceita' ) {
	echo "<INPUT TYPE='hidden' NAME='tipo' VALUE='aceita'>";

	$bem = 	$DadosTransf[NumPat] . "-" . $DadosTransf[Inc] . " - " . substr( $DadosTransf[Descricao], 0, 40 );
	echo "<TD>$bem</TD>";
	echo "<INPUT TYPE='hidden' NAME='numpat' VALUE='$DadosTransf[NumPat]#$DadosTransf[Inc]#$DadosTransf[CodLocal]#$DadosTransf[MatResp]#$DadosTransf[Descricao]'>";
	echo "</TR>";
	echo "<TR>";
	$user = $User[$DadosTransf[MatRespAtual]];
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>DE: </B></TD>";
	echo "<TD>". $Func[$DadosTransf[MatRespAtual]] . "</TD>";
	echo "<INPUT TYPE='hidden' NAME='matatual' VALUE='$DadosTransf[MatRespAtual]'>";
	echo "<INPUT TYPE='hidden' NAME='useratual' VALUE='$user'>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Para onde vai ?</B></TD>";
	echo "<TD><SELECT NAME='local'>";
		echo "<OPTION SELECTED VALUE=''>Selecione o local</OPTION>";
		while ( $DadosLocal = mysql_fetch_array( $rLocal ) ) {
			echo "<OPTION VALUE='$DadosLocal[CodLocal]'>$DadosLocal[DescPredio] / $DadosLocal[DescLocal]</OPTION>";
		}
	echo "</SELECT></TD>";
	echo "</TR>";
}

echo "</TABLE>";
echo "<P>";

//
//	Identifica qual serah a pergunta final
//
if ( $_GET[tipo] != 'pede' ) {
	echo "<P>";
	echo "Aceita ficar com a responsabilidade deste bem ? ";
	echo "<INPUT TYPE='radio' NAME='resposta' VALUE='S'>Sim";
	echo "<INPUT TYPE='radio' NAME='resposta' VALUE='N'>N�o";
	echo "</P>";
}

if ( $_GET[tipo] == 'pede' ) {
	echo "<INPUT TYPE='submit' NAME='grava' VALUE='Enviar solicita��o'>";
} elseif ( $_GET[tipo] == 'aceita' ) { 
	echo "<INPUT TYPE='submit' NAME='grava' VALUE='Aceitar/recusar transfer�ncia'>";
}

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
	form.local.value = string.substring(0,string.indexOf('#'))
	string = string.substring(string.indexOf('#')+1,string.length)
	form.mat.value = string.substring(0,string.indexOf('#'))
}

function verifica( form ) {
	if ( form.tipo.value == 'pede' ) {

		if ( form.numpat.value == '' || form.mat.value == '' ) {
			alert ( "� preciso selecionar o bem a ser transferido e seu novo respons�vel. Volte e complete" )
			return false
		}


	} else {

		if ( !form.resposta[0].checked && !form.resposta[1].checked ) {
			alert ( "Aceita ou n�o o bem? Volte e responda." )
			return false
		}
		if ( form.local.value == '' ) {
			if ( !form.resposta[1].checked ) {
				alert ( "O preenchimento de novo local � obrigat�rio. Volte e complete." )
				return false
			}
		}
	}

	return true
}
</script>