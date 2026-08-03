<?php

namespace Helpers;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(private Validator $validator)
    {
        parent::__construct($validator->first() ?? 'Zadaná data nejsou platná.');
    }

    public function validator(): Validator
    {
        return $this->validator;
    }

    public function errors(): array
    {
        return $this->validator->errors();
    }
}
