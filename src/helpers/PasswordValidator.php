<?php

namespace Helpers;

class PasswordValidator
{
    private int $minimumLength;
    private bool $requireLowercaseLetter;
    private bool $requireUppercaseLetter;
    private bool $requireNumber;
    private bool $requireSpecialCharacter;

    public function __construct(
        int $minimumLength = 10,
        bool $requireLowercaseLetter = true,
        bool $requireUppercaseLetter = true,
        bool $requireNumber = true,
        bool $requireSpecialCharacter = true
    ) {
        $this->minimumLength = $minimumLength;
        $this->requireLowercaseLetter = $requireLowercaseLetter;
        $this->requireUppercaseLetter = $requireUppercaseLetter;
        $this->requireNumber = $requireNumber;
        $this->requireSpecialCharacter = $requireSpecialCharacter;
    }

    public function validate(string $password): array
    {
        $errors = [];

        if (mb_strlen($password) < $this->minimumLength) {
            $errors[] = "min. {$this->minimumLength} znaků";
        }

        if ($this->requireLowercaseLetter && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'malé písmeno';
        }

        if ($this->requireUppercaseLetter && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'velké písmeno';
        }

        if ($this->requireNumber && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'číslo';
        }

        if ($this->requireSpecialCharacter && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'speciální znak';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors)
                ? ''
                : 'Heslo musí obsahovat: ' . implode(', ', $errors) . '.',
        ];
    }
}
