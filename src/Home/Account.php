<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * An account holds tokens for someone. Tokens are whole numbers here, so money
 * arithmetic never touches a float and a total cannot drift by a cent.
 */
final class Account
{
    public function __construct(
        public readonly string $handle,
        public readonly string $holder,
        private int $tokens = 0,
    ) {
        if ($tokens < 0) {
            throw new \RuntimeException('an account starts at zero or above');
        }
    }

    public function tokens(): int
    {
        return $this->tokens;
    }

    public function credit(int $amount, string $why): LedgerLine
    {
        if ($amount < 1) {
            throw new \RuntimeException('a credit carries one token or more');
        }
        $this->tokens += $amount;

        return new LedgerLine($this->handle, 'credit', $amount, $why, $this->tokens);
    }

    public function debit(int $amount, string $why): LedgerLine
    {
        if ($amount < 1) {
            throw new \RuntimeException('a debit carries one token or more');
        }
        if ($amount > $this->tokens) {
            throw new \RuntimeException('the account holds fewer tokens than that');
        }
        $this->tokens -= $amount;

        return new LedgerLine($this->handle, 'debit', $amount, $why, $this->tokens);
    }

    public function transfer(int $amount, Account $to, string $why): array
    {
        if ($to->handle === $this->handle) {
            throw new \RuntimeException('an account cannot send to itself');
        }
        $out = $this->debit($amount, $why);
        $in = $to->credit($amount, $why);

        return [$out, $in];
    }
}
