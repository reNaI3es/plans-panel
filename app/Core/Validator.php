<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Серверна валідація вхідних даних.
 */
final class Validator
{
    private array $errors = [];
    private array $data;
    private array $rules;

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Виконати валідацію.
     * @return bool true якщо все ок
     */
    public function validate(): bool
    {
        $this->errors = [];
        foreach ($this->rules as $field => $ruleSet) {
            $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $this->data[$field] ?? null;
            $label = $field;

            foreach ($rules as $rule) {
                $params = [];
                if (is_string($rule) && str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $this->applyRule($field, $value, $rule, $params);
            }
        }
        return empty($this->errors);
    }

    /**
     * Отримати помилки.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Перша помилка.
     */
    public function firstError(): string
    {
        foreach ($this->errors as $field => $msgs) {
            if (!empty($msgs)) return $msgs[0];
        }
        return '';
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        match ($rule) {
            'required' => $this->require($field, $value),
            'string' => $this->str($field, $value),
            'int', 'integer' => $this->integer($field, $value),
            'email' => $this->email($field, $value),
            'min' => $this->min($field, $value, (int)($params[0] ?? 0)),
            'max' => $this->max($field, $value, (int)($params[0] ?? 0)),
            'in' => $this->in($field, $value, $params),
            'url' => $this->url($field, $value),
            'nullable' => null, // просто дозволяє null
            default => null,
        };
    }

    private function require(string $field, mixed $value): void
    {
        if ($value === null || $value === '' || $value === []) {
            $this->errors[$field][] = "Поле '{$field}' є обов'язковим";
        }
    }

    private function str(string $field, mixed $value): void
    {
        if ($value !== null && !is_string($value)) {
            $this->errors[$field][] = "Поле '{$field}' має бути рядком";
        }
    }

    private function integer(string $field, mixed $value): void
    {
        if ($value !== null && !is_numeric($value)) {
            $this->errors[$field][] = "Поле '{$field}' має бути числом";
        }
    }

    private function email(string $field, mixed $value): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "Поле '{$field}' має бути дійсною email-адресою";
        }
    }

    private function min(string $field, mixed $value, int $min): void
    {
        if (is_string($value) && mb_strlen($value) < $min) {
            $this->errors[$field][] = "Поле '{$field}' має бути мінімум {$min} символів";
        } elseif (is_numeric($value) && $value < $min) {
            $this->errors[$field][] = "Поле '{$field}' має бути не менше {$min}";
        }
    }

    private function max(string $field, mixed $value, int $max): void
    {
        if (is_string($value) && mb_strlen($value) > $max) {
            $this->errors[$field][] = "Поле '{$field}' має бути максимум {$max} символів";
        } elseif (is_numeric($value) && $value > $max) {
            $this->errors[$field][] = "Поле '{$field}' має бути не більше {$max}";
        }
    }

    private function in(string $field, mixed $value, array $allowed): void
    {
        if ($value !== null && $value !== '' && !in_array((string)$value, $allowed, true)) {
            $this->errors[$field][] = "Поле '{$field}' має одне з допустимих значень: " . implode(', ', $allowed);
        }
    }

    private function url(string $field, mixed $value): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = "Поле '{$field}' має бути дійсною URL-адресою";
        }
    }
}
