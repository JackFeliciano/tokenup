# TokenUp

## What the project is

TokenUp checks whether a token's contract design matches its tokenomics before minting, so a founder can see the flaw while the token is still a draft.

Founders define the token, allocate its supply and describe its economic purpose. The review compares both plans, identifies inconsistencies and classifies each finding as a blocker or warning. Blockers prevent minting until the founder makes corrections.

The programme supports credits, debits and transfers. A credit adds tokens when work arrives. A debit takes tokens when a fee falls due, but the account refuses a debit larger than its balance. A transfer moves tokens between 2 accounts and writes 1 ledger line on each side. An account cannot send tokens to itself.

Every ledger line records the account, direction, amount, reason and balance after the transaction. Readers can replay the lines to rebuild account balances without relying on separate balance records.

Token supply changes through minting and burning. A mint raises the supply, while a burn lowers it.

The demo contains 3 accounts, 4 ledger lines and 216 tokens held. The replay matches the account balances.

The programme uses plain PHP 8.3. It requires no Composer and no packages. Tokens use whole-number arithmetic, so no float touches the calculations.

## How to run it

Run the test suite from the repository root:

```bash
php tests/run.php
```


Start the demo with PHP's built-in server:

```bash
php -S 127.0.0.1:8104 -t showroom showroom/index.php
```


The application uses / as its default URL prefix. If a proxy passes a different prefix, set TOKENUP_BASE to that value.

The application uses this setting to account for the URL prefix passed by the proxy.

## What the routes answer

The demo exposes 2 routes.

/ renders the 3 sample accounts, their balances and the ledger.
/health answers with the account count, ledger line count, tokens held and whether ledger replay matches the accounts.

The home route shows the account balances alongside the transaction history. The health route reports the current figures and the result of the replay check.

## How the layout is arranged

The repository separates account and ledger logic from request handling, presentation, tests and deployment files.

src/Home/ holds Account, LedgerLine, Ledger, Token and Sample.
src/App/ holds the router, front controller and HTML.
showroom/ holds the entry file and stylesheet.
tests/ holds the test suite.
deploy/ holds the systemd unit and nginx location block.

The src/Home/ directory contains the account and token data structures and the ledger logic. The src/App/ directory handles routing and page output. The remaining directories contain the demo entry point, test runner and deployment configuration.

## The state of the suite

The repository includes a hand-written test runner with 16 checks.

Run all checks with:

```bash
php tests/run.php
```


The runner requires no package installation or external test framework. It provides a direct way to check the programme from the command line.

## Licence

The repository uses the Mozilla Public License 2.0 (MPL 2.0). You may use, modify and distribute the code. You must publish changes to files covered by the licence.

