<?

//
//	Sistema: Bens patrimoniais
//	Modulo: Relatorio completo por local
//	Concluído em 25/08/2003
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//


//
//	Prepara ambiente
//
require('fcs-gerais.php');
require("./include/patrimonio.conf");
abre_banco( $VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha );
set_time_limit(100);
//
//	Retira o local dos excluídos
//
$excluidos = " WHERE CodLocal != $VAR_Excluido ";

//
//	Pega os nomes dos locais
//
//$qLocal = "SELECT CodLocal, DescLocal
//		FROM $TB_LOCAL
//		$excluidos ";
$qLocal = "select a.$FD_PREDIO_DESC, b.$FD_LOCAL_COD, b.$FD_LOCAL_DESC
		from $TB_PREDIO a, $TB_LOCAL b
		where a.$FD_PREDIO_COD = b.$FD_LOCAL_PREDIO
		order by a.$FD_PREDIO_DESC, b.$FD_LOCAL_DESC";
$rLocal = mysql_query( $qLocal );
$QtdeLocal = mysql_num_rows( $rLocal );
if ( !$rLocal ) {
	trata_erro( "Não foi possível acessar a tabela de locais." );

} elseif ( $QtdeLocal == 0 ) {
	echo "<CENTER><BR>\n";
	echo "<H3>Atenção</H3>\n";
	echo "A tabela de locais está vazia. Informe ao pessoal da Informática.<BR>\n";
	echo "</CENTER><BR>\n";
	exit;

}


//
//	Montando o relatorio em PDF por LOCAL
//
//	

//
//	Definicoes iniciais do relatorio
//
include ("{$VAR_PDF}/class.ezpdf.php");
$pdf =new Cezpdf('a4','landscape');
$pdf->selectFont("{$VAR_PDF}/fonts/Helvetica.afm");

//
//	títulos da tabela. Caso não sejam especificados, os nomes dos campos serão usados.
//
$tabela_titulos = array("NumPat" => "Código", 
                        "Inc" => "Inc",
                        "Descricao" => "Descrição",
                        "Nome" => "Responsável",
                        "CodLocal" => "Local");

//
//	opções da tabela. Cor, tamanho, fonte, etc
//
$tabela_opcoes = array("showLines" => 1, 
	"showHeadings" => 1, 
	"shaded" => 1, 
	"shadeCol" => array(0.75, 0.75, 0.75), 
	"colGap" => 8,
	"width" => 812,
	"fontSize" => 8,
	"cols" => array("NumPat" => array("justification"=>"center",
		"width" => 60),
	"Inc" => array("justification"=>"center",
		"width" => 40),
	"Descricao" => array("justification"=>"left",
		"width" => 360),
	"Responsável" => array("justification"=>"left",
			"width" => 205),
	"CodLocal" => array("justification"=>"center",
			"width" => 197)));

//
//	Aqui eu defino a numeração das páginas. Posição, tamanho de fonte e formato
//
$pdf->ezStartPageNumbers(815, 40, 10, 'left', 'Página {PAGENUM} de {TOTALPAGENUM}');

//
//	margens do documento. A superior é maior porque deve ter espaço para o cabeçalho
//
$pdf->ezSetMargins(120, 60, 15, 15);

//
//	Abre um objeto para colocar o cabecalho em todas as paginas
//
$objeto = $pdf->openObject();
cabec($pdf, 'l'); 
$pdf->closeObject();
$pdf->addObject($objeto, "all");
$pdf->ezsetY(472);


//
//	Monta relatorio
//
WHILE ( $dLocal = mysql_fetch_array( $rLocal ) ) {

	//
	//	Seleciona os bens por local
	//
	//	Cuja situacao seja: ATIVO, A SER ALIENADO, A SER DISPONIBILIZADO e RECEBIDO EM COMODATO
	//
	$qBens = "SELECT a.NumPat, a.Inc, a.Descricao, a.CodLocal, b.$FD_FUNC_NOME
			FROM bemtbcad a, $TB_FUNCIONARIO b
			WHERE a.CodLocal = '$dLocal[$FD_LOCAL_COD]' AND
				a.MatResp = b.Matricula AND
				( a.CodSituacao = 1 OR a.CodSituacao = 2 OR a.CodSituacao = 4 OR a.CodSituacao = 7 )
			ORDER BY a.CodLocal, a.NumPat, a.Inc";
	$rBens = mysql_query( $qBens );
	$Linhas = mysql_num_rows( $rBens );
	if ( !$rBens ) {
		trata_erro( "Não foi possível acessar a tabela de bens patrimoniais." );
	} elseif ( $Linhas == 0 ) {
//		$pdf->ezNewPage();
		continue;
	}
	
	//
	//	Preparando a tabela com os dados
	//
	$Dados = array();
	while ($Dados[] = mysql_fetch_array( $rBens )) {}
	for ( $i=0; $i < $Linhas; $i++ ) {
//		$Dados[$i][CodLocal] = $dLocal[DescLocal];
		$Dados[$i][CodLocal] = $dLocal[$FD_PREDIO_DESC] . " - " . $dLocal[$FD_LOCAL_DESC];
	}
	
	$pdf->ezTable($Dados, $tabela_titulos, "", $tabela_opcoes);
	
	$pdf->ezNewPage();
	
}	

// O pdf está pronto. Basta mandar pra saída.
$pdf->ezStream(array("Content-Disposition"=> "relatorio.pdf", "attach"=>1));



function cabec(&$pdf, $posicao="p") {
GLOBAL $VAR_Unidade;

	$x = ((($posicao=='p') ? 595 : 842) - 110)/2;
	$y = (($posicao=='p') ? 842 : 595) - 52;
//	$pdf->ezImage("../../figuras/sisas-i.jpg");
	$pdf->ezsetY(560);
	$pdf->ezText("Empresa Brasileira de Pesquisa Agropecuária - EMBRAPA",12);
	$pdf->ezText($VAR_Unidade,12);
	$pdf->ezText("Gestão de Patrimônio",12);
	$pdf->setLineStyle(3);
	$pdf->line(15, 510, 815, 505);
	$pdf->ezsetY(505);
	$pdf->eztext("Listagem de Bens patrimoniais por local", 14, array("justification"=>"center") );
}


function cabecalhoEmbrapa(&$pdf, $posicao="p") {
//
//	Cabecalho do relatorio
//
echo "<CENTER><IMG SRC='/figuras/embrapa.gif'></CENTER>";
echo "<P>";
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD COLSPAN=2><B>Empresa Brasileira de Pesquisa Agropecuária - EMBRAPA</B></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD COLSPAN=2><B>$VAR_Unidade</B></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD WIDTH='80%'><B>Gestão de Patrimônio</B></TD>";
echo "<TD WIDTH='20%' ALIGN=right>Data: " . troca_data( date ("Y/m/d" ) ) . "</TD>";
echo "</TR>";
echo "</TABLE>";
echo "<HR>";
echo "<CENTER>";
echo "<H2>TERMO DE RESPONSABILIDADE</H2>\n";
echo "</CENTER>";

echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=8%><B>Empregado:</B></TD>";
echo "<TD>$DadosFunc[0] - $DadosFunc[1]<BR></TD>";
echo "</TR>";
echo "</TABLE>";

}

function Rodape() {
//
//	Rodapé
//
echo "<HR>";
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH='7%' VALIGN=top><B>OBS:</B></TD>";
echo "<TD WIDTH='93%'>em caso de dano ou desaparecimento de bens, avisar imediatamente ao Agente Patrimonial ou à Chefia imediata, bem como promover a comunicação formal do fato ao SPM, por meio do formulário <U>Comunicação de Dano Patrimonial</U>. Não efetuar a transferência de qualquer bem sem a prévia formalização regulamentar.</TD>";
echo "</TR>";
echo "</TABLE>";

echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=40% ALIGN=center><HR><B>Agente patrimonial</B><HR></TD>";
echo "<TD WIDTH=10%></TD>";
echo "<TD WIDTH=50% ALIGN=center><HR><B>Usuário dos bens</B><HR></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD WIDTH=40% ALIGN=center></TD>";
echo "<TD WIDTH=10%></TD>";
echo "<TD WIDTH=50% ALIGN=center>
<P>Declaro pelo presente documento de responsabilidade que conferi e recebi os bens acima relacionados que ficarão sob minha guarda.</TD>";
echo "</TR>";
echo "<TR>";
echo "<TD WIDTH=40% ALIGN=left VALIGN=top><P>Data: " . troca_data( date("Y/m/d") ) . "</P></TD>";
echo "<TD WIDTH=10%></TD>";
echo "<TD WIDTH=50% ALIGN=left VALIGN=top><P>Data: " . troca_data( date("Y/m/d") ) . "</P><BR></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD WIDTH=40% VALIGN=top><HR></TD>";
echo "<TD WIDTH=10%></TD>";
echo "<TD WIDTH=50% ALIGN=center><P><HR>$DadosFunc[1]";
echo "</P></TD>";
echo "</TR>";
echo "</TABLE>";
echo "</P>";
}

?>