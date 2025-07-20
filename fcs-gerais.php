<?php
//
// Funções gerais do sistema
//

// Função para conectar ao banco de dados
function abre_banco($banco, $usuario, $servidor, $senha) {
    $conn = mysqli_connect($servidor, $usuario, $senha, $banco);
    if (!$conn) {
        die("Conexão falhou: " . mysqli_connect_error());
    }
    return $conn;
}

// Função para tratar erros
function trata_erro($mensagem) {
    echo "<CENTER><BR>";
    echo "<H3>Erro</H3>";
    echo $mensagem . "<BR>";
    echo "</CENTER><BR>";
    exit;
}

// Função para formatar data
function troca_data($data) {
    if (empty($data)) return "";
    $partes = explode("-", $data);
    if (count($partes) == 3) {
        return $partes[2] . "/" . $partes[1] . "/" . $partes[0];
    }
    return $data;
}

// Função para cabeçalho padrão
function mc_dados($titulo) {
    echo "<HTML>";
    echo "<HEAD>";
    echo "<TITLE>$titulo</TITLE>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
    echo "</HEAD>";
    echo "<BODY>";
    echo "<CENTER><H2>$titulo</H2></CENTER>";
}

// Função para cabeçalho simples
function mc_simples() {
    echo "<HTML>";
    echo "<HEAD>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
    echo "</HEAD>";
    echo "<BODY>";
}

?>
