<?php
//
// Funções gerais do sistema
//

// Verifica se a função já existe antes de declarar
if (!function_exists('abre_banco')) {
    // Função para conectar ao banco de dados
    function abre_banco($banco, $usuario, $servidor, $senha) {
        $conn = mysqli_init();
        mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
        mysqli_real_connect($conn, $servidor, $usuario, $senha, $banco, 33843, NULL, MYSQLI_CLIENT_SSL);
        
        if (!$conn) {
            die("Conexão falhou: " . mysqli_connect_error());
        }
        return $conn;
    }
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
    return $partes[2] . "/" . $partes[1] . "/" . $partes[0];
}

// Função para formatar data para o banco
function troca_data_mysql($data) {
    if (empty($data)) return "";
    $partes = explode("/", $data);
    return $partes[2] . "-" . $partes[1] . "-" . $partes[0];
}

// Função para cabeçalho simples
function mc_simples() {
    echo "<html>";
    echo "<head>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
    echo "</head>";
    echo "<body bgcolor='#FFFFFF'>";
}

// Função para cabeçalho completo
function mc_completo($titulo) {
    echo "<html>";
    echo "<head>";
    echo "<title>$titulo</title>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
    echo "</head>";
    echo "<body bgcolor='#FFFFFF'>";
}

// Função para rodapé
function mr_rodape() {
    echo "</body>";
    echo "</html>";
} 
