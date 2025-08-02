<?php
//
// Sistema: Bens patrimoniais
// Modulo: Funções gerais do sistema
// Autor: Adil D. Pinto Jr.
// Embrapa Agroindustria de Alimentos
// Atualizado: 2024 - Atualização para MySQLi
//

// Função para abrir conexão com o banco de dados
function abre_banco($banco, $usuario, $servidor, $senha, $porta = null) {
    if ($porta) {
        $conn = mysqli_connect($servidor, $usuario, $senha, $banco, $porta);
    } else {
        $conn = mysqli_connect($servidor, $usuario, $senha, $banco);
    }
    
    if (!$conn) {
        die("Não foi possível conectar ao banco de dados: " . mysqli_connect_error());
    }
    
    // Define o charset para UTF-8
    mysqli_set_charset($conn, "utf8");
    
    return $conn;
}

// Função para tratar erros
function trata_erro($mensagem) {
    echo "<CENTER><BR>";
    echo "<H3>Erro</H3>";
    echo "$mensagem<BR>";
    echo "</CENTER><BR>";
    exit;
}

// Função para montar cabeçalho completo
function mc_dados($titulo = "") {
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<title>Sistema de Controle de Patrimônio - $titulo</title>\n";
    echo "<meta charset='utf-8'>\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1'>\n";
    echo "<style>\n";
    echo "body { font-family: Arial, sans-serif; }\n";
    echo "table { border-collapse: collapse; width: 100%; }\n";
    echo "td, th { border: 1px solid #ddd; padding: 8px; }\n";
    echo "tr:nth-child(even) { background-color: #f2f2f2; }\n";
    echo "</style>\n";
    echo "</head>\n";
    echo "<body>\n";
    if ($titulo) {
        echo "<h2>$titulo</h2>\n";
    }
}

// Função para montar cabeçalho simples
function mc_simples() {
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset='utf-8'>\n";
    echo "</head>\n";
    echo "<body>\n";
}

// Função para montar rodapé
function mr_simples() {
    echo "</body>\n";
    echo "</html>\n";
}

// Função para formatar data (dd/mm/aaaa)
function troca_data($data) {
    if (strlen($data) == 10) {
        return substr($data, 8, 2) . "/" . substr($data, 5, 2) . "/" . substr($data, 0, 4);
    }
    return $data;
}

// Função para validar número de patrimônio
function valida_patrimonio($numero) {
    return preg_match('/^\d{7}$/', $numero);
}

// Função para escapar strings para uso em SQL
function escape_string($conn, $string) {
    return mysqli_real_escape_string($conn, trim($string));
}

// Função para escapar strings para uso em HTML
function escape_html($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

// Função para verificar permissão de acesso
function verifica_permissao($perfil_necessario) {
    if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != $perfil_necessario) {
        trata_erro("Você não tem permissão para acessar esta função.");
        return false;
    }
    return true;
}

// Função para log de atividades
function registra_log($conn, $acao, $descricao) {
    $matricula = escape_string($conn, $_SESSION['mat']);
    $acao = escape_string($conn, $acao);
    $descricao = escape_string($conn, $descricao);
    $data = date('Y-m-d H:i:s');
    
    $query = "INSERT INTO bemtblog (data, matricula, acao, descricao) 
              VALUES ('$data', '$matricula', '$acao', '$descricao')";
    
    mysqli_query($conn, $query);
}
?>
