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
        $TITLE = 'TokenUp, a token project checked before it mints';
        $LEDE = 'TokenUp checks whether a token\'s contract design matches its tokenomics before minting, so a founder can see the flaw while the token is still a draft.';
        $WHAT = 'Founders define the token, allocate its supply and describe its economic purpose. The review compares both plans, identifies inconsistencies and classifies each finding as a blocker or warning. Blockers prevent minting until the founder makes corrections.';

        $project = $sample->project();
        $tokenomics = $sample->tokenomics();
        $review = $sample->review();
        $paper = $sample->paper();

        $out = $this->header($TITLE);
        $out .= '<h1>' . $this->h($TITLE) . '</h1>' . "\n";
        $out .= '<p class="lede">' . $this->h($LEDE) . '</p>' . "\n";
        $out .= '<p>' . $this->h($WHAT) . '</p>' . "\n";
        $out .= '<p class="stage">Project ' . $this->h($project->handle) . ', stage ' . $this->h($project->stage())
            . '. Blocking findings: ' . count($review->blockers())
            . '. Warnings: ' . count($review->warnings())
            . '. Paper: ' . ($paper->drafted() ? $paper->words() . ' words' : 'not drafted') . '.</p>' . "\n";

        $out .= '<h2>The token</h2>' . "\n";
        $out .= '<dl class="facts">';
        foreach ([
            ['Define the token', 'Define the token, allocate its supply and describe its economic purpose.'],
            ['Run the review', 'The review compares the contract design with the tokenomics and classifies each finding as a blocker or a warning.'],
            ['Mint the token', 'Once the review passes, TokenUp generates a white paper from the tokenomics and mints the token.'],
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
