<?php

namespace BinanceAPI\Exceptions;

/**
 * Exceção para erros de validação de parâmetros
 */
class ValidationException extends BinanceException
{
    /** @var array<string> */
    private array $errors;

    /**
     * @param string $message Mensagem de erro
     * @param array<string> $errors Lista de erros de validação
     */
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message, -1000, 400, ['errors' => $errors]);
        $this->errors = $errors;
    }

    /**
     * Retorna a lista de erros de validação
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Cria exceção para campo obrigatório
     */
    public static function requiredField(string $field): self
    {
        return new self(
            "Parâmetro \"{$field}\" é obrigatório",
            [$field => "Campo obrigatório"]
        );
    }

    /**
     * Cria exceção para múltiplos campos obrigatórios
     *
     * @param array<string> $fields
     */
    public static function requiredFields(array $fields): self
    {
        $errors = [];
        foreach ($fields as $field) {
            $errors[$field] = "Campo obrigatório";
        }

        return new self(
            "Parâmetros obrigatórios: " . implode(', ', $fields),
            $errors
        );
    }

    /**
     * Cria exceção para valor inválido
     *
     * @param array<string> $allowedValues
     */
    public static function invalidValue(string $field, string $value, array $allowedValues = []): self
    {
        $message = "Valor inválido para \"{$field}\": {$value}";
        if (!empty($allowedValues)) {
            $message .= ". Valores permitidos: " . implode(', ', $allowedValues);
        }

        return new self($message, [$field => "Valor inválido"]);
    }
}
