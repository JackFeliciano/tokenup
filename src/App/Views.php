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
        $LEDE = 'TokenUp helps founders build, review and launch token projects by checking the token design against its tokenomics before minting.';
        $WHAT = 'TokenUp guides founders through token creation and tokenomics, from contract features and supply limits to allocations, vesting and treasury plans. Its review compares both drafts, identifies inconsistencies and marks each finding as a blocker or warning. Founders resolve blockers before minting, then generate a white paper from the approved tokenomics.';

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
            'Name' => $project->name,
            'Symbol' => $project->symbol,
            'Network' => $project->network->label . ' (' . $project->network->standard . ')',
            'Decimals' => (string) $project->decimals,
            'Initial supply' => number_format($project->initialSupply),
            'Supply' => $project->cap(),
        ] as $term => $value) {
            $out .= '<dt>' . $this->h($term) . '</dt><dd>' . $this->h($value) . '</dd>';
        }
        $out .= '</dl>' . "\n";

        $out .= '<h2>Contract features</h2>' . "\n";
        $out .= '<ul class="features">';
        foreach (['permit', 'mintable', 'burnable', 'pausable', 'unlimitedSupply', 'initialLiquidity'] as $name) {
            $out .= '<li class="feature">' . $this->h($project->features->label($name)) . '</li>';
        }
        $out .= '</ul>' . "\n";

        $out .= '<h2>Tokenomics</h2>' . "\n";
        $out .= '<table class="buckets"><thead><tr><th>Bucket</th><th>Share</th><th>Tokens</th><th>Holder</th><th>Lock</th></tr></thead><tbody>' . "\n";
        foreach ($tokenomics->allocations() as $allocation) {
            $out .= '<tr><td>' . $this->h($allocation->bucket) . '</td><td class="num">'
                . number_format($tokenomics->share($allocation) / 100, 2) . '%</td><td class="num">'
                . number_format($allocation->tokens) . '</td><td>' . $this->h($allocation->holder)
                . '</td><td class="num">' . ($allocation->lockMonths > 0 ? $allocation->lockMonths . ' mo' : 'none')
                . '</td></tr>' . "\n";
        }
        $out .= '</tbody></table>' . "\n";

        $out .= '<h2>The review</h2>' . "\n";
        $out .= '<table class="findings"><thead><tr><th>Level</th><th>Code</th><th>What the review saw</th><th>The fix</th></tr></thead><tbody>' . "\n";
        foreach ($review->findings() as $finding) {
            $out .= '<tr class="' . $this->h($finding->level) . '"><td>' . $this->h($finding->level)
                . '</td><td><code>' . $this->h($finding->code) . '</code></td><td>' . $this->h($finding->saw)
                . '</td><td>' . $this->h($finding->fix) . '</td></tr>' . "\n";
        }
        $out .= '</tbody></table>' . "\n";

        $out .= '<h2>A project the review stopped</h2>' . "\n";
        [$stopped, $stoppedTokenomics, $stoppedReview] = $sample->stopped();
        $out .= '<p class="stopped">' . $this->h($stopped->name) . ' (' . $this->h($stopped->symbol) . ') holds '
            . count($stoppedReview->blockers()) . ' blocking findings, so stage stays ' . $this->h($stopped->stage())
            . ' and the mint stays shut.</p>' . "\n";
        $out .= '<ul class="blockers">';
        foreach ($stoppedReview->blockers() as $finding) {
            $out .= '<li><code>' . $this->h($finding->code) . '</code> ' . $this->h($finding->saw) . '</li>';
        }
        $out .= '</ul>' . "\n";

        $out .= '<h2>The white paper</h2>' . "\n";
        $out .= '<div class="paper">';
        foreach (explode("\n\n", $paper->draft()) as $paragraph) {
            $out .= '<p>' . $this->h($paragraph) . '</p>';
        }
        $out .= '</div>' . "\n";

        $out .= '<h2>What a founder does</h2>' . "\n";
        $out .= '<ol class="steps">' . "\n";
        foreach ([
            ['Define the token', "Define the token's name, symbol, network, supply limits, decimals and contract features."],
            ['Divide the supply', "Divide the supply into allocations, set lock and cliff periods, and describe the token's utility, demand, emission and treasury."],
            ['Run the review', 'Run the review, resolve every blocker, run it again until it passes, then generate the white paper and mint the token.'],
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
