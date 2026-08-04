<?php

namespace Services\AI;

interface AiClientInterface
{
    public function name(): string;
    public function ask(string $system, string $user): string;

    /** Lehký ping – stačí krátká odpověď */
    public function testConnection(): bool;

    /** Stav klíče/modelu atd. */
    public function getStatus(): array;
}
