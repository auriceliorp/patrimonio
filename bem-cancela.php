<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Cancelamento de solicitacoes
//	Concluído em 12/04/2004
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
settype($_GET['cod'],"int");
mc_dados( "Cancelamento de solicitações" );

$Texto = "Usuário(a),<BR>";
$Texto = $Texto . "Este módulo permite cancelar suas solicitação de alienação, disponibilização ou transferência ainda pendentes.<BR>";

//
//	Primeira vez que entra no formulario
//
if ( $_GET[tipo] == 'inicio' ) {


	//
	//	Pega os empregados ATIVOS
	//
	$qFunc = "select $FD_FUNC_MAT, $FD_FUNC_NOME
			from $TB_FUNCIONARIO
			where $V_FUNC_ATIVO
			order by $FD_FUNC_NOME";

	$rFunc = mysql_query( $qFunc );
	if ( !$rFunc ) {
		trata_erro( "Não foi possível acessar a tabela de empregados." );
	} elseif ( mysql_num_rows( $rFunc ) == 0 ) {
		echo "<CENTER><BR>\n";
		echo "<H3>Atenção</H3>\n";
		echo "A tabela de empregados está vazia. Informe ao pessoal da Informática.<BR>\n";
		echo "</CENTER><BR>\n";
		exit;
	} elseif ( mysql_num_rows( $rFunc ) >= 0 ) {
		WHILE ( $dFunc = mysql_fetch_array( $rFunc ) ) {
			$Func[$dFunc[$FD_FUNC_MAT]] = $dFunc[$FD_FUNC_NOME];
		}
		mysql_free_result( $rFunc );
	}


	//
	//	Pega as solicitacoes pendentes do USUARIO
	//
	$qPendencia = "SELECT a.*, b.Descricao
			FROM bemtbmodificacao a, bemtbcad b
			WHERE a.MatRespAtual = '$_SESSION[mat]' AND
				a.DtTroca = '0000-00-00' AND
				a.NumPat = b.NumPat AND a.Inc = b.Inc
			ORDER BY CodSituacaoNovo, DtPedido";
	@$rPendencia = mysql_query( $qPendencia );
	if ( !$rPendencia ) {
		trata_erro( "Não foi possível acessar a tabela de solicitações." );
	}
	if ( mysql_num_rows( $rPendencia ) == 0 ) {
		echo "<CENTER>";
		echo "<B>Você não tem solicitações pendentes!</B>\n";
		echo "</CENTER>";
		exit;
	}

	//
	//	Mostra as pendencias
	//
	echo $Texto;
	echo "<CENTER><P>";
	echo "<TABLE BORDER WIDTH=100%>";
	echo "<TR>";
	echo "<TD BGCOLOR='#C0C0C0' VALIGN='top'><B>Solicitações</B></TD>";
	echo "</TR>";

	while ( $Dados = mysql_fetch_array( $rPendencia) ) {
		if ( $Dados[CodSituacaoNovo] == 4 ) {
			$tipo = "Alienação";
		} elseif ( $Dados[CodSituacaoNovo] == 7 ) {
			$tipo = "Disponibilização";
		} elseif ( $Dados[CodSituacaoNovo] == 0 ) {
			$tipo = "Transferência de responsabilidade";
		}
		echo "<TR>";
		if ( $Dados[CodSituacaoNovo] == 0 ) {
			echo "<TD><A HREF='./bem-cancela.php?tipo=cancela&cod=$Dados[CodModifica]&desc=$Dados[Descricao]'>$tipo do patrimônio<BR> $Dados[NumPat]-$Dados[Inc] - $Dados[Descricao]<BR>Para: " . $Func[$Dados[MatRespNovo]] . "</A></TD>";
		} else {
			echo "<TD><A HREF='./bem-cancela.php?tipo=cancela&cod=$Dados[CodModifica]&desc=$Dados[Descricao]'>$tipo do patrimônio<BR> $Dados[NumPat]-$Dados[Inc] - $Dados[Descricao]</A></TD>";
		}
		echo "</TR>";
	}

	echo "</TABLE>";
	echo "</P></CENTER>";


//
//	Resposta do Formulario
//
//
} elseif ( $_GET[tipo] == 'cancela' ) {

	echo "<FORM ACTION='./bem-cancela.php?tipo=confirma' METHOD='post'>";

	echo "<CENTER>";
	echo "Clique no botão <B>Cancelar solicitação</B><BR> para excluir a solicitação referente ao patrimônio<BR> <B>$_GET[desc]</B>.";
	echo "<CENTER><P>";
	echo "<INPUT TYPE='hidden' NAME='cod' VALUE='$_GET[cod]'>";
	echo "<INPUT TYPE='button' VALUE='Voltar' ONCLICK='self.history.back();'>";
	echo "<INPUT TYPE='submit' NAME='grava' VALUE='Cancelar solicitação'>";
	echo "</P></CENTER>";

	echo "</FORM>";

//
//	Resposta do Formulario
//
//
} elseif ( $_GET[tipo] == 'confirma' ) {

	$qPesq = "DELETE FROM bemtbmodificacao
			WHERE CodModifica = '$_POST[cod]'";
	@$rPesq = mysql_query( $qPesq );
	if ( !$rPesq ) {
		trata_erro( "Não foi excluir a solicitação." );
	} else {
		echo "<CENTER>";
		echo "<B>Solicitação excluída com sucesso!</B>\n";
		echo "</CENTER>";
	}

}	

mr_simples();

?>
