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

    public function toFloat(): float
    {
        return $this->cents / 100;
    }

    public function formatted(string $currencySymbol = 'R$'): string
    {
        return sprintf('%s %s', $currencySymbol, number_format($this->toFloat(), 2, ',', '.'));
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    public function equals(Money $other): bool
    {
        return $this->cents === $other->toCents();
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function add(Money $other): self
    {
        return new self($this->cents + $other->toCents());
    }

    public function isLessThan(Money $other): bool
    {
        return $this->cents < $other->toCents();
    }

    public function subtract(Money $other): self
    {
        return new self($this->cents - $other->toCents());
    }
}
