<?php

//
//	Sistema: Bens patrimoniais
//	Modulo: Apresentacao dos bens patrimoniais do usuario logado
//	Concluído em 25/08/2003
//	ALTERADO: 15/12/2008
//	ALTERADO: 05/01/2010
//  ALTERADO: 2024 - Atualização para MySQLi
//	Autor: Adil D. Pinto Jr.
//	Embrapa Agroindustria de Alimentos
//


//
//	Verifica se tem algum usuario logado no momento
//
session_start();
if (!isset($_SESSION['mat'])) {
    die("Voce precisa se logar primeiro!");
}

//
//	Prepara ambiente
//
require("fcs-gerais.php");
require("./include/patrimonio.conf");
$conn = abre_banco($VAR_Banco, $VAR_Usuario, $VAR_Servidor, $VAR_Senha);
mc_dados("Listagem dos bens patrimoniais sob sua responsabilidade");


//
//	Pega os nomes dos locais
//
$qLocal = "select a.$FD_PREDIO_DESC, b.$FD_LOCAL_COD, b.$FD_LOCAL_DESC
        from $TB_PREDIO a, $TB_LOCAL b
        where a.$FD_PREDIO_COD = b.$FD_LOCAL_PREDIO";
$rLocal = mysqli_query($conn, $qLocal);
if (!$rLocal) {
    trata_erro("Não foi possível acessar a tabela de locais.");
} elseif (mysqli_num_rows($rLocal) == 0) {
    echo "<CENTER><BR>\n";
    echo "<H3>Atenção</H3>\n";
    echo "A tabela de locais está vazia. Informe ao pessoal da Informática.<BR>\n";
    echo "</CENTER><BR>\n";
    exit;
} elseif (mysqli_num_rows($rLocal) >= 0) {
    $Local = array();
    while ($temp = mysqli_fetch_array($rLocal)) {
        $cod = $temp[$FD_LOCAL_COD];
        $Local[$cod] = $temp[$FD_PREDIO_DESC] . " - " . $temp[$FD_LOCAL_DESC];
    }
    mysqli_free_result($rLocal);
}

//
//	Seleciona os bens sob sua responsabilidade
//
$qBens = "select $FD_PAT_NUMPAT, $FD_PAT_INC, $FD_PAT_DESC, $FD_PAT_LOCAL
        from $TB_PATRIMONIO
        where $FD_PAT_RESP = '" . mysqli_real_escape_string($conn, $_SESSION['mat']) . "'
        order by $FD_PAT_NUMPAT, $FD_PAT_INC";

$rBens = mysqli_query($conn, $qBens);
if (!$rBens) {
    trata_erro("Não foi possível acessar a tabela de bens patrimoniais.");
} elseif (mysqli_num_rows($rBens) == 0) {
    echo "<CENTER><BR>\n";
    echo "<H3>Atenção</H3>\n";
    echo "Não há bens patrimoniais sob sua responsabilidade.<BR>\n";
    echo "</CENTER><BR>\n";
    exit;
}


//
//	Mostrando a listagem de bens
//
//

//
//	Identificacao do empregado
//
echo "<TABLE WIDTH=650>";
echo "<TR>";
echo "<TD WIDTH=8%><B>Nome:</B></TD>";
echo "<TD>" . htmlspecialchars($_SESSION['mat']) . " - " . htmlspecialchars($_SESSION['nome']) . "<BR></TD>";
echo "</TABLE>";

//
//	Cabecalho
//
echo "<P>";
echo "<TABLE WIDTH=100%>";
echo "<TR>";
echo "<TD WIDTH=12% BGCOLOR='#C0C0C0'><B>Código</B></TD>";
echo "<TD WIDTH=60% BGCOLOR='#C0C0C0'><B>Descrição</B></TD>";
echo "<TD WIDTH=28% BGCOLOR='#C0C0C0'><B>Local</B></TD>";
echo "</TR>";

//
//	Dados
//
while ($dBens = mysqli_fetch_array($rBens)) {
    echo "<TR>";
    echo "<TD WIDTH=12% VALIGN=top>" . htmlspecialchars($dBens[$FD_PAT_NUMPAT]) . "-" . htmlspecialchars($dBens[$FD_PAT_INC]) . "</TD>";
    echo "<TD WIDTH=60% VALIGN=top>" . htmlspecialchars($dBens[$FD_PAT_DESC]) . "</TD>";
    echo "<TD WIDTH=28% VALIGN=top>" . htmlspecialchars($Local[$dBens[$FD_PAT_LOCAL]]) . "</TD>";
    echo "</TR>";
}
echo "</TABLE>";

mr_simples();

?>
