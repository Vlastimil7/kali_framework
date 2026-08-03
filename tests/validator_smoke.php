<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/vendor/autoload.php';

use Helpers\Flash;
use Helpers\Toast;
use Helpers\ValidationException;
use Helpers\Validator;

$_SESSION = [];

$valid = Validator::make([
    'name' => 'Anna Nováková',
    'email' => 'anna@example.test',
    'age' => 30,
    'password' => '12345678',
    'password_confirm' => '12345678',
    'role' => 'admin',
    'terms' => 'on',
    'phone' => '',
    'slug' => 'darkovy-poukaz',
    'profile' => ['city' => 'Praha'],
    'ignored' => 'not validated',
], [
    'name' => 'required|string|min:2|max:100',
    'email' => 'required|email',
    'age' => 'required|integer|between:18,120',
    'password' => 'required|string|min:8',
    'password_confirm' => 'required|same:password',
    'role' => 'in:user,admin',
    'terms' => 'accepted',
    'phone' => 'nullable|string|min:6',
    'slug' => 'slug',
    'profile.city' => 'required|string',
    'optional' => 'sometimes|required',
]);

if (!$valid->passes() || $valid->errors() !== []) {
    throw new RuntimeException('Validator rejected valid data.');
}
$validated = $valid->validated();
if (
    isset($validated['ignored'])
    || ($validated['email'] ?? null) !== 'anna@example.test'
    || ($validated['profile']['city'] ?? null) !== 'Praha'
) {
    throw new RuntimeException('Validator::validated() returned unexpected fields.');
}

$conditionalOptional = Validator::make([], [
    'company_id' => 'required_if:type,company|string',
    'password_confirm' => 'required_with:password|nullable|string|same:password',
]);
if ($conditionalOptional->fails()) {
    throw new RuntimeException('Inactive conditional rules rejected missing values.');
}

$conditionalRequired = Validator::make([
    'type' => 'company',
    'password' => '12345678',
    'password_confirm' => '',
], [
    'company_id' => 'required_if:type,company|string',
    'password_confirm' => 'bail|required_with:password|nullable|string|same:password',
]);
if (!$conditionalRequired->has('company_id') || !$conditionalRequired->has('password_confirm')) {
    throw new RuntimeException('Active conditional rules accepted missing values.');
}

$invalid = Validator::make([
    'name' => ' ',
    'email' => 'invalid',
    'password' => '1234567',
    'password_confirm' => 'different',
    'role' => 'owner',
    'terms' => '0',
    'price' => '12.999',
    'slug' => 'already-used',
], [
    'name' => 'bail|required|string',
    'email' => 'required|email',
    'password' => 'required|string|min:8',
    'password_confirm' => 'same:password',
    'role' => 'in:user,admin',
    'terms' => 'accepted',
    'price' => ['required', 'regex:/^\d+([.,]\d{1,2})?$/'],
    'slug' => [
        'required',
        static fn ($value): string|bool => $value === 'already-used' ? 'Slug už existuje.' : true,
    ],
], [
    'email.email' => 'Neplatný e-mail.',
], [
    'name' => 'jméno',
]);

if (!$invalid->fails()) {
    throw new RuntimeException('Validator accepted invalid data.');
}
foreach (['name', 'email', 'password', 'password_confirm', 'role', 'terms', 'price', 'slug'] as $field) {
    if (($invalid->errors()[$field] ?? []) === []) {
        throw new RuntimeException("Validator did not report {$field}.");
    }
}
if ($invalid->first('email') !== 'Neplatný e-mail.' || $invalid->first('slug') !== 'Slug už existuje.') {
    throw new RuntimeException('Custom validation messages failed.');
}

try {
    $invalid->validate();
    throw new RuntimeException('Invalid validation did not throw.');
} catch (ValidationException $exception) {
    if (($exception->errors()['email'] ?? []) === []) {
        throw new RuntimeException('ValidationException did not expose errors.');
    }
}

$invalid->flash('registration', [
    'email' => 'invalid',
    'password' => 'must-not-be-stored',
]);
$old = Flash::old('registration');
$errors = Validator::flashedErrors('registration');
$toasts = Toast::all();

if (($old['email'] ?? null) !== 'invalid' || isset($old['password'])) {
    throw new RuntimeException('Validator flash input did not filter sensitive fields.');
}
if (($errors['email'] ?? []) === [] || ($toasts[0]['type'] ?? null) !== 'error') {
    throw new RuntimeException('Validator flash errors or toast failed.');
}

try {
    Validator::make(['field' => 'value'], ['field' => 'unknown_rule'])->passes();
    throw new RuntimeException('Unknown validation rule was silently accepted.');
} catch (InvalidArgumentException) {
}

echo "validator smoke tests passed\n";
