<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Manutencao do cadastro de bens patrimoniais
//	Concluído em 25/08/2003
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

mc_dados( "Cadastro de bens patrimoniais" );



//
//	Primeira vez que entra no formulario
//
if ( !$_POST[grava] ) {

	//
	//	Levanta dados
	//
	if ( $_POST[numpat] == "" ) {
		echo "<CENTER>";
		echo "<H3>Atenção</H3>";
		echo "Você precisa digitar um número de patrimônio.<BR>\n";
		echo "</CENTER>";
		exit;

	} else {

		//
		//	Pega os dados do bem patrimonial
		//
		$qEquip = "select *
				from $TB_PATRIMONIO
				where $FD_PAT_NUMPAT = '$_POST[numpat]' and
					$FD_PAT_INC = '$_POST[inc]' ";
		@$rEquip = mysql_query( $qEquip );
		if ( !$rEquip ) {
			trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
		} else {
			if ( mysql_num_rows( $rEquip ) > 0 ) {
				$ExisteBem = 1;
				$DadosEquip = mysql_fetch_array( $rEquip );
				mysql_free_result( $rEquip );
			} else {
				$ExisteBem = 0;
			}
		}


		//
		//	Pega a matricula e o nome dos empregados
		//
		if ( $ExisteBem ) {
			$condicao = "";
		} elseif ( !$ExisteBem ) {
			$condicao = " where $V_FUNC_ATIVO ";
		}

		$qFunc = "select $FD_FUNC_MAT, $FD_FUNC_NOME
				from $TB_FUNCIONARIO	
				$condicao
				order by $FD_FUNC_NOME";
		@$rFunc = mysql_query( $qFunc );
		if ( !$rFunc ) {
			trata_erro( "Não foi possível acessar a tabela de empregados." );
		} else {
			while ( $temp = mysql_fetch_array( $rFunc ) ) {
				$Func[$temp[$FD_FUNC_MAT]] = $temp[$FD_FUNC_NOME];
			}
		}
		mysql_free_result( $rFunc );


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
		//	Pega os nomes das situacoes
		//
		$qSitua = "select * 
				from $TB_SITUACAO 
				order by $FD_SIT_DESC";
		$rSitua = mysql_query( $qSitua );
		if ( !$rSitua ) {
			trata_erro( "Não foi possível acessar a tabela de situações." );
		} elseif ( mysql_num_rows( $rSitua ) == 0 ) {
			echo "<CENTER><BR>\n";
			echo "<H3>Atenção</H3>\n";
			echo "A tabela de situações está vazia. Informe ao pessoal da Informática.<BR>\n";
			echo "</CENTER><BR>\n";
			exit;
		} else {
			while ( $temp3 = mysql_fetch_array( $rSitua ) ) {
				$Situacao[$temp3[$FD_SIT_COD]] = $temp3[$FD_SIT_DESC];
			}
		}
		mysql_free_result( $rSitua );


		//
		//	Pega os nomes dos tipos
		//
		$qTipo = "select *
				from $TB_TIPO
				order by $FD_TIPO_DESC";
		$rTipo = mysql_query( $qTipo );
		if ( !$rTipo ) {
			trata_erro( "Não foi possível acessar a tabela de tipo de patrimonio." );
		} elseif ( mysql_num_rows( $rTipo ) == 0 ) {
			echo "<CENTER><BR>\n";
			echo "<H3>Atenção</H3>\n";
			echo "A tabela de tipos tipos de patrimonio está vazia. Informe ao pessoal da Informática.<BR>\n";
			echo "</CENTER><BR>\n";
			exit;
		}

	}


//======================================================
//
//	Resposta do Formulario
//

//
//	GRAVACAO dos dados
//
} elseif ( $_POST[grava] ) {

	//
	//	Preparando variaveis
	//
	$DataHoje = date( "Y/m/d" );
	$_POST[descricao] = addslashes( $_POST[descricao] );
	$_POST[desctecnica] = addslashes( $_POST[desctecnica] );
	$_POST[obs] = addslashes( $_POST[obs] );
	$_POST[dtcompra] = troca_data( $_POST[dtcompra] );
	$_POST[dtgarantia] = troca_data( $_POST[dtgarantia] );

	//
	//	Se for INCLUSAO e os campso TIPO, LOCAL e RESPONSAVEL estiverem vazios
	//		NAO permitir gravação
	//
	if ( $_POST[tipo] == '0' || $_POST[codlocal] == '0' || $_POST[matresp] == '0' ) {
		echo "<CENTER>";
		echo"<H3>Gravação</H3>";
		echo "<B>Você deixou alguns campos obrigatórios sem informação. Volte e altere</B>";
		echo "</CENTER>";
		exit;			
	}


	//
	//	Incluindo registro, se NAO EXISTE um bem
	//
	if ( !$_POST[ExisteBem] ) {
		$qBem = "INSERT INTO bemtbcad
				(NumPat, Inc, Conta, Marca, Modelo, Descricao, DescTecnica, Obs, DtCompra, DtGarantia, Valor, CodLocal, MatResp, CodSituacao, CodTipo)
				VALUES ('$_POST[numpat]', '$_POST[inc]', '$_POST[conta]', '$_POST[marca]', '$_POST[modelo]', '$_POST[descricao]', '$_POST[desctecnica]', '$_POST[obs]', '$_POST[dtcompra]', '$_POST[dtgarantia]', '$_POST[valor]', '$_POST[codlocal]', '$_POST[matresp]', 1, '$_POST[tipo]')";
		@$ResultBem = mysql_query( $qBem );

		if ( $ResultBem ) {
			echo "<CENTER>";
			echo"<H3>Gravação</H3>";
			echo "<B>Bem patrimonial inserido com sucesso</B>";
			echo "</CENTER>";
			exit;
		} else {
			trata_erro( "Não foi possível inserir o bem patrimonial." );
		}
		

	//
	//	Alterando registro, se EXISTE um bem
	//
	} else {

		//
		//	Atualizando o bem patrimonial
		//
		$qBem = "update $TB_PATRIMONIO
				set Marca = '$_POST[marca]',
					Conta = '$_POST[conta]',
					Modelo = '$_POST[modelo]',
					Descricao = '$_POST[descricao]',
					DescTecnica = '$_POST[desctecnica]',
					Obs = '$_POST[obs]',
					DtCompra = '$_POST[dtcompra]',
					DtGarantia = '$_POST[dtgarantia]',
					Valor = '$_POST[valor]',
					CodTipo = '$_POST[tipo]'
				where $FD_PAT_NUMPAT = '$_POST[numpat]' and $FD_PAT_INC = '$_POST[inc]' ";
		@$ResultBem = mysql_query( $qBem );

		if ( $ResultBem ) {
			echo "<CENTER>";
			echo"<H3>Gravação</H3>";
			echo "<B>Bem patrimonial atualizado/cadastrado com sucesso</B>";
			echo "</CENTER>";
			exit;
		} else {
			trata_erro( "Não foi possível atualizar os dados." );
		}

	}

}	

echo "<FORM ACTION='./bem-cad.php' METHOD='post' onSubmit='return verifica( this )'>";
	if ( !$ExisteBem ) {
		echo "<P><B>Bem inexistente!</B><BR>\n";
		echo "Para cadastrá-lo, digite os dados e clique no botão <U>Gravar dados</U>, no final do formulário.</P>\n";
	}

	echo "<TABLE BORDER>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>No. patrimonial: </B></TD>";
	if ( $ExisteBem ) {
		echo "<TD>$DadosEquip[NumPat]-$DadosEquip[Inc]</TD>";
		echo "<INPUT TYPE='hidden' NAME='numpat' VALUE='$DadosEquip[NumPat]'>";
		echo "<INPUT TYPE='hidden' NAME='inc' VALUE='$DadosEquip[Inc]'>";
	} else {
		echo "<TD><INPUT TYPE='text' NAME='numpat' VALUE='$_POST[numpat]' SIZE=7 MAXLENGTH=7> - <INPUT TYPE='text' NAME='inc' VALUE='$_POST[inc]' SIZE=3></TD>";
	}
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Descrição do bem: </B></TD>";
	echo "<TD><TEXTAREA ROWS='3' NAME='descricao' COLS='55'>" . stripslashes( $DadosEquip[Descricao] ) . "</TEXTAREA></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Dados técnicos: </B></TD>";
	echo "<TD><TEXTAREA ROWS='3' NAME='desctecnica' COLS='55'>" . stripslashes( $DadosEquip[DescTecnica] ) . "</TEXTAREA></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Observações: </B></TD>";
	echo "<TD><TEXTAREA ROWS='3' NAME='obs' COLS='55'>" . stripslashes( $DadosEquip[Obs] ) . "</TEXTAREA></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Conta: </B></TD>";
	echo "<TD><INPUT TYPE='text' NAME='conta' VALUE='" . stripslashes ( $DadosEquip[Conta] ) . "' SIZE=4></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Marca: </B></TD>";
	echo "<TD><INPUT TYPE='text' NAME='marca' VALUE='" . stripslashes ( $DadosEquip[Marca] ) . "' SIZE=30></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Modelo: </B></TD>";
	echo "<TD><INPUT TYPE='text' NAME='modelo' VALUE='$DadosEquip[Modelo]' SIZE=30 MAXLENGTH=30></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Data de compra: </B></TD>";
	echo "<TD><INPUT TYPE='text' NAME='dtcompra' VALUE='" . troca_data($DadosEquip[DtCompra]) . "' SIZE=10 MAXLENGTH=10> Formato: dd-mm-aaaa</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Garantia: até </B></TD>";
	echo "<TD><INPUT TYPE='text' NAME='dtgarantia' VALUE='" . troca_data($DadosEquip[DtGarantia]) . "' SIZE=10 MAXLENGTH=10> Formato: dd-mm-aaaa</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Valor: </B></TD>";
	echo "<TD><INPUT TYPE='text' NAME='valor' VALUE='$DadosEquip[Valor]' SIZE=12 MAXLENGTH=12></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD WIDTH=30% VALIGN='top' BGCOLOR='#C0C0C0'><B>Tipo de bem:</B></TD>";
	echo "<TD><SELECT NAME='tipo'>";
		echo "<OPTION SELECTED VALUE= ''>Selecione</OPTION>";
		while ( $Dados = mysql_fetch_array( $rTipo ) ) {
			if ( $Dados[CodPatTipo] == $DadosEquip[CodTipo] ) {
				echo "<OPTION SELECTED VALUE= '$Dados[CodPatTipo]'>$Dados[DescPatTipo]</OPTION>";
			} else {
				echo "<OPTION VALUE= '$Dados[CodPatTipo]'>$Dados[DescPatTipo]</OPTION>";
			}
		}
	echo "</SELECT></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD WIDTH=30% VALIGN='top' BGCOLOR='#C0C0C0'><B>Situação de bem:</B></TD>";
	if ( $ExisteBem ) {
		echo "<TD>" . $Situacao[$DadosEquip[CodSituacao]] . "</TD>";
	} else {
		echo "<TD>Ativo</TD>";
		echo "<INPUT TYPE='hidden' NAME='situacao' VALUE='1'>";
	}
	echo "</SELECT></TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Localização: </B></TD>";
	if ( $ExisteBem ) {
		echo "<TD>" . $Local[$DadosEquip[CodLocal]] . "</TD>";
	} else {
		echo "<TD><SELECT NAME='codlocal'>";
			reset( $Local );
			echo "<OPTION SELECTED VALUE= ''>Selecione</OPTION>";
			do {
				echo "<OPTION VALUE= " . key( $Local ) . ">" . $Local[ key( $Local ) ] . "</OPTION>\n";
			} while ( next( $Local ) ) ;
		echo "</SELECT></TD>";
	}
	echo "</TR>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' WIDTH=30% VALIGN='top'><B>Responsável: </B></TD>";
	if ( $ExisteBem ) {
		echo "<TD>" . $Func[$DadosEquip[MatResp]] . "</TD>";
	} else {
		echo "<TD><SELECT NAME='matresp'>";
			reset( $Func );
			echo "<OPTION SELECTED VALUE= ''>Selecione</OPTION>";
			do {
				echo "<OPTION VALUE= " . key($Func) . ">" . $Func[key($Func)] . "</OPTION>\n";
			} while ( next( $Func ) ) ;
			echo "</SELECT></TD>";
	}
	echo "</TR>";
	echo "</TABLE>";

	echo "<P>";
	echo "<CENTER>";
	echo "<INPUT TYPE='hidden' NAME='tecnicoatual' VALUE='$DadosEquip[MatResp]'>";
	echo "<INPUT TYPE='hidden' NAME='localatual' VALUE='$DadosEquip[CodLocal]'>";
	echo "<INPUT TYPE='hidden' NAME='ExisteBem' VALUE='$ExisteBem'>";

	echo "<INPUT TYPE='button' VALUE='Voltar' ONCLICK='self.history.back();'>";
	echo "<INPUT TYPE='reset' NAME='limpa' VALUE='Limpar formul&aacute;rio'>";
	echo "<INPUT TYPE='submit' NAME='grava' VALUE='Gravar dados'>";
	echo "</CENTER>";

echo "</FORM>";

//echo "</BODY>";
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