<?php

namespace Helpers;

class EmailValidator
{
    private array $blockedDomains = [
        'mailinator.com',
        'tempmail.com',
        '10minutemail.com',
        'guerrillamail.com',
        'example.com',
        'example.cz',
        'test.cz',
    ];

    public function validate(string $email): array
    {
        $normalizedEmail = trim(strtolower($email));

        if ($normalizedEmail === '') {
            return [
                'valid' => false,
                'message' => 'Zadejte e-mail.',
                'domain' => null,
            ];
        }

        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'valid' => false,
                'message' => 'Zadejte platný e-mail.',
                'domain' => null,
            ];
        }

        $emailDomain = $this->getDomain($normalizedEmail);

        if ($emailDomain === null) {
            return [
                'valid' => false,
                'message' => 'Zadejte platný e-mail.',
                'domain' => null,
            ];
        }

        if ($this->isBlockedDomain($emailDomain)) {
            return [
                'valid' => false,
                'message' => 'Použijte prosím běžnou e-mailovou adresu.',
                'domain' => $emailDomain,
            ];
        }

        if (!$this->domainHasDnsRecord($emailDomain)) {
            return [
                'valid' => false,
                'message' => 'Zadejte existující e-mailovou doménu.',
                'domain' => $emailDomain,
            ];
        }

        return [
            'valid' => true,
            'message' => 'E-mail je platný.',
            'domain' => $emailDomain,
        ];
    }

    private function getDomain(string $email): ?string
    {
        $atPosition = strrpos($email, '@');

        if ($atPosition === false) {
            return null;
        }

        $emailDomain = substr($email, $atPosition + 1);
        $emailDomain = trim(strtolower($emailDomain));

        return $emailDomain !== '' ? $emailDomain : null;
    }

    private function isBlockedDomain(string $emailDomain): bool
    {
        return in_array($emailDomain, $this->blockedDomains, true);
    }

    private function domainHasDnsRecord(string $emailDomain): bool
    {
        return checkdnsrr($emailDomain, 'MX') || checkdnsrr($emailDomain, 'A');
    }
}
