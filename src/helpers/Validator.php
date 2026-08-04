<?php

namespace Helpers;

use Closure;
use DateTime;

class Validator
{
    private array $errors = [];
    private bool $validated = false;
    private array $currentRuleNames = [];

    private const MESSAGES = [
        'required' => 'Pole :attribute je povinné.',
        'required_if' => 'Pole :attribute je povinné.',
        'required_with' => 'Pole :attribute je povinné.',
        'accepted' => 'Pole :attribute musí být potvrzeno.',
        'string' => 'Pole :attribute musí být text.',
        'integer' => 'Pole :attribute musí být celé číslo.',
        'numeric' => 'Pole :attribute musí být číslo.',
        'boolean' => 'Pole :attribute musí mít hodnotu ano/ne.',
        'array' => 'Pole :attribute musí být seznam.',
        'email' => 'Pole :attribute musí obsahovat platnou e-mailovou adresu.',
        'url' => 'Pole :attribute musí obsahovat platnou URL adresu.',
        'min' => 'Pole :attribute musí mít minimálně :min znaků nebo hodnotu :min.',
        'max' => 'Pole :attribute může mít maximálně :max znaků nebo hodnotu :max.',
        'between' => 'Pole :attribute musí být mezi :min a :max.',
        'size' => 'Pole :attribute musí mít velikost :size.',
        'in' => 'Vybraná hodnota pole :attribute není povolena.',
        'not_in' => 'Vybraná hodnota pole :attribute není povolena.',
        'same' => 'Pole :attribute se musí shodovat s polem :other.',
        'different' => 'Pole :attribute musí být jiné než pole :other.',
        'confirmed' => 'Potvrzení pole :attribute se neshoduje.',
        'regex' => 'Pole :attribute má neplatný formát.',
        'date' => 'Pole :attribute musí obsahovat platné datum.',
        'date_format' => 'Pole :attribute neodpovídá formátu :format.',
        'alpha' => 'Pole :attribute může obsahovat pouze písmena.',
        'alpha_num' => 'Pole :attribute může obsahovat pouze písmena a číslice.',
        'slug' => 'Pole :attribute může obsahovat pouze malá písmena, číslice a pomlčky.',
    ];

    private function __construct(
        private array $data,
        private array $rules,
        private array $messages = [],
        private array $attributes = [],
    ) {
    }

    public static function make(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = [],
    ): self {
        return new self($data, $rules, $messages, $attributes);
    }

    public function passes(): bool
    {
        $this->run();

        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        $this->run();

        return $this->errors;
    }

    public function first(?string $field = null): ?string
    {
        $this->run();

        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return null;
    }

    public function all(): array
    {
        $messages = [];
        foreach ($this->errors() as $fieldMessages) {
            array_push($messages, ...$fieldMessages);
        }

        return $messages;
    }

    public function has(string $field): bool
    {
        return ($this->errors()[$field] ?? []) !== [];
    }

    public function validated(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }

        $validated = [];
        foreach (array_keys($this->rules) as $field) {
            if ($this->hasValue((string)$field)) {
                $this->setValue($validated, (string)$field, $this->value((string)$field));
            }
        }

        return $validated;
    }

    public function validate(): array
    {
        return $this->validated();
    }

    public function flash(string $form, array $input, string $title = 'Chyba formuláře'): self
    {
        if ($this->fails()) {
            Flash::set('validation_errors.' . $form, $this->errors());
            Flash::withInput($form, $input);
            Toast::error($this->first() ?? 'Zkontrolujte zadané údaje.', $title);
        }

        return $this;
    }

    public static function flashedErrors(string $form): array
    {
        $errors = Flash::get('validation_errors.' . $form, []);

        return is_array($errors) ? $errors : [];
    }

    private function run(): void
    {
        if ($this->validated) {
            return;
        }
        $this->validated = true;

        foreach ($this->rules as $field => $fieldRules) {
            $rules = $this->normalizeRules($fieldRules);
            $exists = $this->hasValue($field);
            $value = $this->value($field);
            $ruleNames = array_map(
                static fn ($rule): string => is_string($rule) ? strtolower(strtok($rule, ':')) : '',
                $rules,
            );
            $this->currentRuleNames = $ruleNames;

            if (in_array('sometimes', $ruleNames, true) && !$exists) {
                continue;
            }

            $required = $this->isRequired($rules);
            if (!$exists && !$required) {
                continue;
            }

            if (in_array('nullable', $ruleNames, true) && !$required && ($value === null || $value === '')) {
                continue;
            }

            foreach ($rules as $rule) {
                if (is_string($rule) && in_array(strtolower($rule), ['bail', 'sometimes', 'nullable'], true)) {
                    continue;
                }

                $error = $rule instanceof Closure
                    ? $this->validateClosure($rule, $field, $value)
                    : $this->validateRule((string)$rule, $field, $value, $exists);

                if ($error !== null) {
                    $this->errors[$field][] = $error;
                    if (in_array('bail', $ruleNames, true)) {
                        break;
                    }
                }
            }
        }
    }

    private function validateClosure(Closure $rule, string $field, $value): ?string
    {
        $result = $rule($value, $field, $this->data);
        if ($result === true || $result === null) {
            return null;
        }

        return is_string($result) ? $result : $this->message($field, 'invalid');
    }

    private function validateRule(string $definition, string $field, $value, bool $exists): ?string
    {
        [$rule, $parameterString] = array_pad(explode(':', $definition, 2), 2, '');
        $rule = strtolower(trim($rule));
        $parameters = $parameterString === '' ? [] : str_getcsv($parameterString, ',', '"', '');

        $valid = match ($rule) {
            'required' => $exists && !$this->isEmpty($value),
            'required_if' => $this->requiredIf($parameters) ? !$this->isEmpty($value) : true,
            'required_with' => $this->requiredWith($parameters) ? !$this->isEmpty($value) : true,
            'accepted' => in_array($value, ['yes', 'on', '1', 1, true, 'true'], true),
            'string' => is_string($value),
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'numeric' => is_numeric($value),
            'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false', 'on', 'off'], true),
            'array' => is_array($value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'min' => $this->sizeOf($value) >= (float)($parameters[0] ?? 0),
            'max' => $this->sizeOf($value) <= (float)($parameters[0] ?? 0),
            'between' => $this->between($value, $parameters),
            'size' => $this->sizeOf($value) === (float)($parameters[0] ?? 0),
            'in' => in_array((string)$value, array_map('strval', $parameters), true),
            'not_in' => !in_array((string)$value, array_map('strval', $parameters), true),
            'same' => $value === $this->value((string)($parameters[0] ?? '')),
            'different' => $value !== $this->value((string)($parameters[0] ?? '')),
            'confirmed' => $value === $this->value($field . '_confirmation'),
            'regex' => $parameterString !== '' && @preg_match($parameterString, (string)$value) === 1,
            'date' => is_string($value) && strtotime($value) !== false,
            'date_format' => $this->matchesDateFormat((string)$value, (string)($parameters[0] ?? '')),
            'alpha' => is_string($value) && preg_match('/^[\pL\s]+$/u', $value) === 1,
            'alpha_num' => is_string($value) && preg_match('/^[\pL\pN]+$/u', $value) === 1,
            'slug' => is_string($value) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1,
            default => throw new \InvalidArgumentException("Neznámé validační pravidlo: {$rule}"),
        };

        return $valid ? null : $this->message($field, $rule, $parameters);
    }

    private function message(string $field, string $rule, array $parameters = []): string
    {
        $template = $this->messages[$field . '.' . $rule]
            ?? $this->messages[$rule]
            ?? self::MESSAGES[$rule]
            ?? 'Pole :attribute není platné.';

        $replace = [
            ':attribute' => $this->attributes[$field] ?? str_replace('_', ' ', $field),
            ':min' => $parameters[0] ?? '',
            ':max' => $parameters[1] ?? $parameters[0] ?? '',
            ':size' => $parameters[0] ?? '',
            ':other' => $this->attributes[$parameters[0] ?? ''] ?? ($parameters[0] ?? ''),
            ':format' => $parameters[0] ?? '',
        ];

        return strtr($template, $replace);
    }

    private function normalizeRules(string|array $rules): array
    {
        return is_string($rules) ? explode('|', $rules) : $rules;
    }

    private function requiredIf(array $parameters): bool
    {
        $other = array_shift($parameters);
        if ($other === null) {
            return false;
        }

        return in_array((string)$this->value($other), array_map('strval', $parameters), true);
    }

    private function requiredWith(array $parameters): bool
    {
        foreach ($parameters as $field) {
            if ($this->hasValue($field) && !$this->isEmpty($this->value($field))) {
                return true;
            }
        }

        return false;
    }

    private function isRequired(array $rules): bool
    {
        foreach ($rules as $definition) {
            if (!is_string($definition)) {
                continue;
            }

            [$rule, $parameterString] = array_pad(explode(':', $definition, 2), 2, '');
            $rule = strtolower(trim($rule));
            $parameters = $parameterString === '' ? [] : str_getcsv($parameterString, ',', '"', '');

            if ($rule === 'required') {
                return true;
            }
            if ($rule === 'required_if' && $this->requiredIf($parameters)) {
                return true;
            }
            if ($rule === 'required_with' && $this->requiredWith($parameters)) {
                return true;
            }
        }

        return false;
    }

    private function between($value, array $parameters): bool
    {
        $size = $this->sizeOf($value);
        $min = (float)($parameters[0] ?? 0);
        $max = (float)($parameters[1] ?? $min);

        return $size >= $min && $size <= $max;
    }

    private function sizeOf($value): float
    {
        if (is_array($value)) {
            return count($value);
        }
        if (
            is_int($value)
            || is_float($value)
            || (is_numeric($value) && array_intersect(['integer', 'numeric'], $this->currentRuleNames) !== [])
        ) {
            return (float)$value;
        }

        return (float)mb_strlen((string)$value);
    }

    private function matchesDateFormat(string $value, string $format): bool
    {
        if ($format === '') {
            return false;
        }

        $date = DateTime::createFromFormat('!' . $format, $value);

        return $date !== false && $date->format($format) === $value;
    }

    private function isEmpty($value): bool
    {
        return $value === null
            || (is_string($value) && trim($value) === '')
            || $value === [];
    }

    private function hasValue(string $field): bool
    {
        $sentinel = new \stdClass();

        return $this->value($field, $sentinel) !== $sentinel;
    }

    private function value(string $field, $default = null)
    {
        $value = $this->data;
        foreach (explode('.', $field) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private function setValue(array &$target, string $field, $value): void
    {
        $segments = explode('.', $field);
        $cursor = &$target;

        foreach ($segments as $index => $segment) {
            if ($index === array_key_last($segments)) {
                $cursor[$segment] = $value;
                break;
            }

            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }
            $cursor = &$cursor[$segment];
        }
    }
}
