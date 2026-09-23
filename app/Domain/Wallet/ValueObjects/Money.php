<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

final readonly class Money
{
    private int $cents;

    private function __construct(int $cents)
    {
        $this->cents = $cents;
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function toCents(): int
    {
        return $this->cents;
    }
}
