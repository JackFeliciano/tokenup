## Roadmap

TokenUp keeps whole-number token accounts and one ledger that a reader can replay into the balances. A transfer writes both sides. The hand-written runner carries 16 checks.

1. Add a statement per account, so a member reads the lines that touched the balance in order.
2. Add a closing balance per period, so two readers can compare the ledger at the same day.
3. Add signed requests, so a member can move tokens from an endpoint this program does not host.
4. Add a supply report, so the mint and burn history of a token stands in one place.
5. Expand the hand-written runner to cover a transfer that fails on the second side, a replay across a long history, and a mint that overflows the supply.
