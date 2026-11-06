<?php

declare(strict_types=1);

namespace Tokenup\App;

use Tokenup\Home\Account;
use Tokenup\Home\Sample;

/**
 * The HTML. Every string that reaches the page goes through h(), so a holder name
 * with an ampersand in it cannot break the markup.
 */
final class Views
{
    public function __construct(private readonly string $base = '/')
    {
    }

    private function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function header(string $title): string
    {
        $base = $this->h($this->base);
        $t = $this->h($title);

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$t}</title>
<base href="{$base}">
<link rel="stylesheet" href="assets/site.css">
</head>
<body>
<main>
HTML;
    }

    public function footer(): string
    {
        return "</main>\n</body>\n</html>\n";
    }

    public function page(Sample $sample): string
    {
        $TITLE = 'TokenUp, tokens for work done';
        $LEDE = 'TokenUp keeps whole-number token accounts for network members and records every credit, debit and transfer in a ledger.';
        $WHAT = 'Each member has an account that holds tokens, which represent a unit of work and carry a symbol. A credit adds tokens when work arrives. A debit takes tokens when a fee falls due, but the account refuses any debit larger than its balance. Members can transfer tokens between accounts, but cannot send tokens to themselves. Each ledger line records the account, direction, amount, reason and resulting balance. Readers can replay the lines to rebuild every account balance.';

        $out = $this->header($TITLE);
        $out .= '<h1>' . $this->h($TITLE) . '</h1>' . "\n";
        $out .= '<p class="lede">' . $this->h($LEDE) . '</p>' . "\n";
        $out .= '<p>' . $this->h($WHAT) . '</p>' . "\n";
        $token = $sample->token();
        $out .= '<p class="stage">Token ' . $this->h($token->symbol) . ' prices ' . $this->h($token->unit)
            . '. Supply: ' . $token->supply() . '. Accounts: ' . count($sample->accounts())
            . '. Lines: ' . $sample->ledger()->count() . '. Held: ' . $sample->held()
            . '. Replay matches the accounts: ' . ($sample->agrees() ? 'yes' : 'no') . '.</p>' . "\n";

        $out .= '<div class="accounts">' . "\n";
        foreach ($sample->accounts() as $account) {
            $out .= '<article class="account">' . "\n";
            $out .= '<h2>' . $this->h($account->holder) . '</h2>' . "\n";
            $out .= '<p class="holder">Handle ' . $this->h($account->handle) . '.</p>' . "\n";
            $out .= '<dl class="counts"><dt>Balance</dt><dd class="balance">' . $account->tokens()
                . ' ' . $this->h($token->symbol) . '</dd>'
                . '<dt>Replayed</dt><dd class="balance">' . $sample->ledger()->replay($account->handle) . '</dd></dl>' . "\n";
            $out .= '</article>' . "\n";
        }
        $out .= '</div>' . "\n";

        $out .= '<h2>The ledger</h2>' . "\n";
        $out .= '<table class="ledger"><thead><tr><th>Handle</th><th>Move</th><th>Amount</th><th>Why</th><th>Balance</th></tr></thead><tbody>' . "\n";
        foreach ($sample->ledger()->lines() as $line) {
            $out .= '<tr><td>' . $this->h($line->handle) . '</td><td>' . $this->h($line->direction)
                . '</td><td class="num">' . $line->signed() . '</td><td>' . $this->h($line->why)
                . '</td><td class="num">' . $line->balance . '</td></tr>' . "\n";
        }
        $out .= '</tbody></table>' . "\n";

        $out .= '<h2>What a member can do</h2>' . "\n";
        $out .= '<ol class="steps">' . "\n";
        foreach ([
            ['Open an account', 'Create a token account for a network member.'],
            ['Earn tokens', 'Credit the account when work arrives.'],
            ['Pay a fee', 'Debit the account when a fee falls due, subject to its balance.'],
            ['Send tokens', 'Transfer tokens to another account and record both sides of the transfer.'],
            ['Replay the ledger', 'Rebuild account balances from the recorded ledger lines.'],
        ] as [$step, $line]) {
            $out .= '<li><strong>' . $this->h($step) . '</strong> ' . $this->h($line) . '</li>' . "\n";
        }
        $out .= '</ol>' . "\n";
        $out .= $this->footer();

        return $out;
    }

    public function missing(): string
    {
        return $this->header('Not found') . "<h1>Not found</h1>\n<p>That page does not exist here.</p>\n" . $this->footer();
    }
}
