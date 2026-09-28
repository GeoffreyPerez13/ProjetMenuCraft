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

    public static function validateUsername(string $username): array
    {
        $errors = [];
        if (mb_strlen($username) < 3) {
            $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
        }
        if (mb_strlen($username) > 30) {
            $errors[] = 'Le nom d\'utilisateur ne doit pas dépasser 30 caractères.';
        }
        if (!preg_match('/^[a-zA-Z]/', $username)) {
            $errors[] = 'Le nom d\'utilisateur doit commencer par une lettre.';
        }
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            $errors[] = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, points, tirets et underscores.';
        }
        if (preg_match('/[._-]{2,}/', $username)) {
            $errors[] = 'Le nom d\'utilisateur ne peut pas contenir deux caractères spéciaux consécutifs.';
        }
        $reserved = ['admin', 'administrator', 'root', 'superadmin', 'super_admin', 'moderator', 'menucraft', 'system', 'support', 'null', 'undefined', 'test'];
        if (in_array(strtolower($username), $reserved)) {
            $errors[] = 'Ce nom d\'utilisateur est réservé.';
        }
        return $errors;
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
