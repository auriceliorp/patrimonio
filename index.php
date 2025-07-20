<?php
//
// Sistema: Bens patrimoniais
// Modulo: Principal
// Concluído em 25/08/2003
// Autor: Adil D. Pinto Jr.
// Embrapa Agroindustria de Alimentos
//

session_start();
$_SESSION['mat'] = '340044';
$_SESSION['nome'] = 'Marco Paiva';

require("fcs-gerais.php");
require("./include/patrimonio.conf");
mc_simples();

if ( $_GET['tipo'] == 'aceita' ) {
    echo "<frameset rows='26%,74%' border=0 name='geral'>";
    echo "<frame src='../cabecalho.php?titulo=Bens Patrimoniais - $VAR_Versao' name='cabecalho'>";
    echo "<frameset cols='20%,80%'>";
    echo "<frame src='./bem-menu.php' name='menu'>";
    echo "<frame src='./bem-pesquisa.php?tipo=transf' name='dados'>";
    echo "</frameset>";
    echo "</frameset>";

} elseif ( $_GET['tipo'] == 'transf-pendente' ) {
    echo "<frameset rows='26%,74%' border=0 name='geral'>";
    echo "<frame src='../cabecalho.php?titulo=Bens Patrimoniais - $VAR_Versao' name='cabecalho'>";
    echo "<frameset cols='20%,80%'>";
    echo "<frame src='./bem-menu.php' name='menu'>";
    echo "<frame src='./bem-pesquisa.php?tipo=transf-pendente' name='dados'>";
    echo "</frameset>";
    echo "</frameset>";

} elseif ( $_GET['tipo'] == 'termo-pendente' ) {
    echo "<frameset rows='26%,74%' border=0 name='geral'>";
    echo "<frame src='../cabecalho.php?titulo=Bens Patrimoniais - $VAR_Versao' name='cabecalho'>";
    echo "<frameset cols='20%,80%'>";
    echo "<frame src='./bem-menu.php' name='menu'>";
    echo "<frame src='./bem-rel1.php' name='dados'>";
    echo "</frameset>";
    echo "</frameset>";

} elseif ( $_GET['tipo'] == 'aliena-pendente' ) {
    echo "<frameset rows='26%,74%' border=0 name='geral'>";
    echo "<frame src='../cabecalho.php?titulo=Bens Patrimoniais - $VAR_Versao' name='cabecalho'>";
    echo "<frameset cols='20%,80%'>";
    echo "<frame src='./bem-menu.php' name='menu'>";
    echo "<frame src='./bem-pesquisa.php?tipo=aliena-conf' name='dados'>";
    echo "</frameset>";
    echo "</frameset>";

} else {
    // Página inicial padrão
    echo "<html>";
    echo "<head>";
    echo "<title>Sistema de Controle de Patrimônio - $VAR_Versao</title>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
    echo "</head>";
    echo "<body>";
    
    echo "<frameset rows='26%,74%' border=0 name='geral'>";
    echo "<frame src='../cabecalho.php?titulo=Bens Patrimoniais - $VAR_Versao' name='cabecalho'>";
    echo "<frameset cols='20%,80%'>";
    echo "<frame src='./bem-menu.php' name='menu'>";
    echo "<frame src='./bem-pesquisa.php' name='dados'>";
    echo "</frameset>";
    echo "</frameset>";

    echo "</body>";
    echo "</html>";
}

?>
