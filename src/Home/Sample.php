<?php

declare(strict_types=1);

namespace Tokenup\Home;

/**
 * The project the demo shows. Every figure on the page comes out of these
 * objects, including the findings the review produced.
 */
final class Sample
{
    private Draft $draft;
    private Network $network;
    private Project $project;
    private Tokenomics $tokenomics;
    private Review $review;
    private WhitePaper $paper;

    public function __construct()
    {
        $this->network = new Network('ethereum', 'Ethereum', 'erc-20', 18);
        $this->draft = new Draft('ana-reis');
        $this->draft->set('name', 'Kestrel Works');
        $this->draft->set('symbol', 'KST');
        $this->draft->set('network', 'ethereum');
        $this->draft->set('decimals', 18);
        $this->draft->set('initialSupply', 1_000_000);
        $this->draft->set('maximumSupply', 5_000_000);
        $this->draft->set('permit', true);
        $this->draft->set('mintable', true);
        $this->draft->set('burnable', true);
        $this->draft->set('pausable', false);
        $this->draft->set('unlimitedSupply', false);
        $this->draft->set('initialLiquidity', true);

        $this->project = Project::fromDraft($this->draft, $this->network);

        $this->tokenomics = new Tokenomics(1_000_000, 12, 6);
        $this->tokenomics->allocate(new Allocation('community', 400_000, 'Kestrel Works', 0));
        $this->tokenomics->allocate(new Allocation('liquidity', 200_000, 'Kestrel Works', 0));
        $this->tokenomics->allocate(new Allocation('treasury', 200_000, 'Kestrel Works', 6));
        $this->tokenomics->allocate(new Allocation('team', 150_000, 'Three founders', 12));
        $this->tokenomics->allocate(new Allocation('investors', 50_000, 'Seed round', 12));
        $this->tokenomics->use('utility', 'A holder pays for an audit with KST and the network burns half of the fee.');
        $this->tokenomics->use('demand', 'Every audit order settles in KST, so the work bought each month sets the demand.');
        $this->tokenomics->use('emission', 'The treasury releases a slice of its bucket each quarter under the lock the founders agreed.');
        $this->tokenomics->use('treasury', 'The treasury holds two hundred thousand tokens to fund the first year of audits.');

        $this->review = new Review($this->draft, $this->project, $this->tokenomics);
        $this->project->check($this->review);
        $this->project->adjust();
        $this->paper = new WhitePaper($this->project, $this->tokenomics, $this->review);
        $this->paper->draft();
    }

    public function draft(): Draft { return $this->draft; }
    public function network(): Network { return $this->network; }
    public function project(): Project { return $this->project; }
    public function tokenomics(): Tokenomics { return $this->tokenomics; }
    public function review(): Review { return $this->review; }
    public function paper(): WhitePaper { return $this->paper; }

    /** A second project that stopped at the review. */
    public function stopped(): array
    {
        $draft = new Draft('bright-field');
        $draft->set('name', 'Bright Field Energy');
        $draft->set('symbol', 'BFE');
        $draft->set('network', 'ethereum');
        $draft->set('decimals', 18);
        $draft->set('initialSupply', 2_000_000);
        $draft->set('maximumSupply', 2_000_000);
        $draft->set('permit', false);
        $draft->set('mintable', false);
        $draft->set('burnable', false);
        $draft->set('pausable', true);
        $draft->set('unlimitedSupply', false);
        $draft->set('initialLiquidity', true);
        $project = Project::fromDraft($draft, $this->network);
        $tokenomics = new Tokenomics(1_500_000, 0, 0);
        $tokenomics->allocate(new Allocation('community', 900_000));
        $tokenomics->allocate(new Allocation('team', 600_000));
        $tokenomics->use('utility', 'A holder pays a settlement fee in BFE.');
        $tokenomics->use('demand', 'Power purchase agreements settle in BFE each month.');
        $tokenomics->use('emission', 'The company releases tokens as new sites come online.');
        $tokenomics->use('treasury', 'The company keeps a reserve for the first year of grid work.');
        $review = new Review($draft, $project, $tokenomics);

        return [$project, $tokenomics, $review];
    }
}
