<?php
class Validator {
    private $errors = [];
    
    public function required($field, $value, $message = null) {
        if (empty($value)) {
            $this->errors[$field] = $message ?? "O campo {$field} é obrigatório";
        }
        return $this;
    }
    
    public function email($field, $value, $message = null) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Email inválido";
        }
        return $this;
    }
    
    public function min($field, $value, $min, $message = null) {
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = $message ?? "Mínimo de {$min} caracteres";
        }
        return $this;
    }
    
    public function cpf($field, $value, $message = null) {
        if (!empty($value) && !$this->isValidCPF($value)) {
            $this->errors[$field] = $message ?? "CPF inválido";
        }
        return $this;
    }
    
    public function phone($field, $value, $message = null) {
        if (!empty($value) && !preg_match('/^\(\d{2}\)\s\d{4,5}-\d{4}$/', $value)) {
            $this->errors[$field] = $message ?? "Telefone inválido";
        }
        return $this;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    private function isValidCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}
