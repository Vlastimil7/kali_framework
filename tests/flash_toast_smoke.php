<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/vendor/autoload.php';

use Helpers\Flash;
use Helpers\Toast;

$_SESSION = [];

Flash::set('old_form', ['email' => 'anna@example.test']);
if (!Flash::has('old_form')) {
    throw new RuntimeException('Flash::has() did not find stored data.');
}
if (Flash::peek('old_form')['email'] !== 'anna@example.test') {
    throw new RuntimeException('Flash::peek() returned invalid data.');
}
if (Flash::get('old_form')['email'] !== 'anna@example.test' || Flash::has('old_form')) {
    throw new RuntimeException('Flash::get() did not consume stored data.');
}

Flash::withInput('registration', [
    'email' => 'anna@example.test',
    'password' => 'secret-password',
    'profile' => [
        'name' => 'Anna',
        'api_key' => 'secret-key',
    ],
    'internal_note' => 'do not keep',
], ['internal_note']);

$oldInput = Flash::old('registration');
if (($oldInput['email'] ?? null) !== 'anna@example.test') {
    throw new RuntimeException('Flash::old() did not return stored input.');
}
if (isset($oldInput['password']) || isset($oldInput['internal_note']) || isset($oldInput['profile']['api_key'])) {
    throw new RuntimeException('Flash::withInput() stored sensitive or excluded input.');
}
if (($oldInput['profile']['name'] ?? null) !== 'Anna' || Flash::old('registration') !== []) {
    throw new RuntimeException('Flash old input was not recursively preserved and consumed.');
}

Flash::withInput('contact', ['email' => 'anna@example.test']);
Flash::clearOld('contact');
if (Flash::old('contact') !== []) {
    throw new RuntimeException('Flash::clearOld() did not remove old input.');
}

Flash::set('nullable', null);
if (!Flash::has('nullable') || Flash::pull('nullable', 'fallback') !== null) {
    throw new RuntimeException('Flash does not preserve explicit null values.');
}

Toast::success('Uloženo');
Toast::warning('Email se nepodařilo odeslat');

$toasts = Toast::all();
if (count($toasts) !== 2) {
    throw new RuntimeException('Toast queue did not preserve all messages.');
}
if ($toasts[0]['type'] !== 'success' || $toasts[1]['type'] !== 'warning') {
    throw new RuntimeException('Toast queue returned messages in an invalid order.');
}
if (Toast::all() !== []) {
    throw new RuntimeException('Toast::all() did not consume the queue.');
}

echo "Flash/Toast smoke test passed.\n";
