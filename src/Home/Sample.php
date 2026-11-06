<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The accounts, the token and the ledger the demo shows. Every figure on the page
 * comes out of these objects.
 */
final class Sample
{
    /** @var array<string, Account> */
    private array $accounts = [];
    private Ledger $ledger;
    private Token $token;

    private const OPENING = ['tu-1' => 120, 'tu-2' => 64, 'tu-3' => 40];

    public function __construct()
    {
        $this->token = new Token('TUP', 'hour', 24000);
        $this->ledger = new Ledger();
        foreach ([['tu-1', 'Ana Reis', 120], ['tu-2', 'Bright Field', 64], ['tu-3', 'Kestrel Works', 40]] as [$h, $name, $start]) {
            $this->accounts[$h] = new Account($h, $name, $start);
        }
        $this->ledger->record(
            ...$this->accounts['tu-1']->transfer(40, $this->accounts['tu-2'], 'hours booked'),
        );
        $this->ledger->record(
            $this->accounts['tu-2']->debit(24, 'network fee'),
        );
        $this->ledger->record(
            $this->accounts['tu-3']->credit(16, 'work delivered'),
        );
    }

    /** @return list<Account> */
    public function accounts(): array
    {
        return array_values($this->accounts);
    }

    public function ledger(): Ledger
    {
        return $this->ledger;
    }

    public function token(): Token
    {
        return $this->token;
    }

    public function held(): int
    {
        $sum = 0;
        foreach ($this->accounts as $account) {
            $sum += $account->tokens();
        }

        return $sum;
    }

    /** The balance an account opened with, before any line moved it. */
    private function opening(string $handle): int
    {
        return self::OPENING[$handle] ?? 0;
    }

    public function agrees(): bool
    {
        foreach ($this->accounts as $account) {
            if ($this->opening($account->handle) + $this->ledger->replay($account->handle) !== $account->tokens()) {
                return false;
            }
        }

        return true;
    }
}
