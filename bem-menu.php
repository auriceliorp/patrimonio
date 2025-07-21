<?php
//
// Sistema: Bens patrimoniais
// Modulo: Gerador do menu do sistema
//

// Verifica se tem algum usuario logado no momento
if (!isset($_SESSION['mat'])) {
    die("Você precisa se logar primeiro!");
}

// Preparar o ambiente
if (!isset($VAR_Banco)) {
    require("./include/patrimonio.conf");
}

// Verifica se é super usuário
$is_super_user = isset($_SESSION['super_user']) && $_SESSION['super_user'];

// Verifica o perfil do usuário
$acesso = $_SESSION['perfil'];

//=====================================================
// OPCOES DO USUARIO
echo "<P><B>Usuário</B><BR>";
echo "<HR>";
echo "<a href='bem-meusbens.php' target='dados'>Meus bens</a><BR>";
echo "<a href='bem-transf.php?tipo=pede' target='dados'>Solicita transf.</a><BR>";
echo "<a href='bem-pesquisa.php?tipo=transf' target='dados'>Aceita transf.</a><BR>";
echo "<a href='bem-local.php' target='dados'>Altera local</a><BR>";
echo "<a href='bem-aliena-sol.php' target='dados'>Solicita alienação</a><BR>";
echo "<a href='bem-cancela.php?tipo=inicio' target='dados'>Cancelamento</a><BR>";

//===========================================
// OPCOES DO PATRIMONIO
if ($is_super_user || $acesso == $PERFIL_Agente || $acesso == $PERFIL_Inventario) {
    echo "<P><B>Patrimônio</B><BR>";
    echo "<HR>";
    echo "<a href='bem-pesquisa.php?tipo=transf-pendente' target='dados'>Transf. pendentes</a><BR>";
    echo "<a href='bem-transf-agente.php?user=$acesso' target='dados'>Realiza transf.</a><BR>";
    echo "<a href='bem-pesquisa.php?tipo=aliena-conf' target='dados'>Aceita alienação</a><BR>";
    echo "<a href='bem-pesquisa.php?tipo=aliena-fim' target='dados'>Finaliza alienação</a><BR>";
    echo "<a href='bem-local-agente.php' target='dados'>Troca de Local</a><BR>";
    echo "<a href='bem-trocasituacao.php' target='dados'>Troca de situação</a> - NOVO<BR>";
    echo "<a href='bem-local.php' target='dados'>Cadastro de locais</a> - NOVO<BR>";
}

//===========================================
// OPCOES DA COMISSAO
if ($is_super_user || $acesso == $PERFIL_Agente || $acesso == $PERFIL_Inventario) {
    echo "<P><B>Comissão</B><BR>";
    echo "<HR>";
    echo "<a href='bem-local-comissao.php' target='dados'>Alteração de local</a><BR>";
    echo "<a href='bem-pesquisa.php?tipo=transf-pendente' target='dados'>Transf. pendentes</a><BR>";
}

//===============================================
// RELATORIOS
if ($is_super_user || $acesso == $PERFIL_Agente || $acesso == $PERFIL_Chefia || $acesso == $PERFIL_Inventario) {
    echo "<P><B>Relatório</B><BR>";
    echo "<HR>";
    echo "<a href='bem-rel1.php' target='dados'>Transf. concluídas</a><BR>";
    echo "<a href='bem-rel2.php' target='dados'>Termo individual</a><BR>";
    echo "<a href='bem-rel3.php' target='dados'>Bens por situação</a><BR>";
    echo "<a href='bem-rel6.php' target='dados'>Bens por tipo</a><BR>";
    echo "<a href='bem-rel4.php' target='dados'>Listagem geral</a><BR>";
    echo "<a href='bem-rel5.php' target='dados'>Bens por área</a><BR>";
    echo "<a href='bem-rel7pdf.php' target='novo'>Completo por local</a><BR>";
    echo "<a href='bem-rel8-ativo.php' target='dados'>Empreg. sem bens</a><BR>";
    echo "<a href='bem-rel8-inativo.php' target='dados'>Inativos com bens</a><BR>";
    echo "<a href='bem-rel9.php' target='dados'>Bens por local</a><BR>";
}

//================================================
// PESQUISA POR BEM
if ($is_super_user || $acesso == $PERFIL_Agente || $acesso == $PERFIL_Inventario) {
    echo "<P><B>Cadastro</B><BR>";
    echo "<HR>";
    echo "Digite o número do bem a pesquisar:";
    echo "<FORM ACTION='bem-cad.php' TARGET='dados' METHOD='post'>";
    echo "<INPUT TYPE='text' NAME='numpat' SIZE='7' MAXLENGTH=7>";
    echo "<INPUT TYPE='text' NAME='inc' SIZE='3' MAXLENGTH=3 VALUE='000'>";
    echo "<INPUT TYPE='submit' NAME='submit' VALUE='Pesquisa'>";
    echo "</FORM>";
}
?>
