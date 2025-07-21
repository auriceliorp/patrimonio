<?php
//
// Funções gerais do sistema
//

// Função para conectar ao banco de dados
if (!function_exists('abre_banco')) {
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
if (!function_exists('trata_erro')) {
    function trata_erro($mensagem) {
        echo "<center><br><h3>Erro</h3>$mensagem<br></center><br>";
        exit;
    }
}

// Funções de data
if (!function_exists('troca_data')) {
    function troca_data($data) {
        if (empty($data)) return "";
        $partes = explode("-", $data);
        return $partes[2] . "/" . $partes[1] . "/" . $partes[0];
    }
}

if (!function_exists('troca_data_mysql')) {
    function troca_data_mysql($data) {
        if (empty($data)) return "";
        $partes = explode("/", $data);
        return $partes[2] . "-" . $partes[1] . "-" . $partes[0];
    }
}
?> 
