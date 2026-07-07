<?php
/**
 * Validator — Validation de formulaires
 */
class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $this->applyRule($field, $value, $rule, $params);
            }
        }
        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        $label = str_replace('_', ' ', $field);
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "Le champ $label est requis.";
                }
                break;
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "L'adresse email n'est pas valide.";
                }
                break;
            case 'min':
                if (!empty($value) && mb_strlen($value) < (int)$params[0]) {
                    $this->errors[$field][] = "Le champ $label doit contenir au moins {$params[0]} caractères.";
                }
                break;
            case 'max':
                if (!empty($value) && mb_strlen($value) > (int)$params[0]) {
                    $this->errors[$field][] = "Le champ $label ne doit pas dépasser {$params[0]} caractères.";
                }
                break;
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = "Le champ $label doit être un nombre.";
                }
                break;
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (($_POST[$confirmField] ?? '') !== $value) {
                    $this->errors[$field][] = "La confirmation ne correspond pas.";
                }
                break;
        }
    }

    public static function validatePassword(string $password): array
    {
        $errors = [];
        if (mb_strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial.';
        }
        return $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return '';
    }
}
