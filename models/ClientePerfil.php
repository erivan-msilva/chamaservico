<?php
require_once 'core/Database.php';

class ClientePerfil {
    public $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Buscar endereços do cliente
    public function buscarEnderecos($clienteId) {
        $sql = "SELECT * FROM tb_endereco WHERE pessoa_id = ? ORDER BY principal DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    // Criar novo endereço
    public function criarEndereco($dados) {
        $sql = "INSERT INTO tb_endereco (pessoa_id, cep, logradouro, numero, complemento, bairro, cidade, estado, principal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['pessoa_id'],
            $dados['cep'],
            $dados['logradouro'],
            $dados['numero'],
            $dados['complemento'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['principal']
        ]);
    }
}
?>
