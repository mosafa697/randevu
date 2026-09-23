# GH Issues Skill (task-by-task workflow)

One task at a time, end to end. Never work two tasks together.

## Task lifecycle (follow in order, every task)

1. **Refinement** — read `gh issue view <n>`, confirm the "Done when" list is clear and small. If vague, ask the user before coding.
2. **Planning** — short plan: files to touch, tests to add, how to verify. Keep it in the todo list.
3. **Claim** — assign yourself and move `ready` → `in-progress` BEFORE editing:
   ```powershell
   gh issue edit <n> --add-assignee "@me" --add-label "in-progress" --remove-label "ready"
   # NOTE (PowerShell): quote the label values or the CLI misparses them.
   gh issue view <n>  # re-check: if assignee is not you, stop
   ```
4. **Implement** — scoped to the issue's "Done when" only. Multiple commits are fine and encouraged (one logical step per commit).
5. **Review** — re-read your own diff: accidental scope creep, leftover debug, Blade directive misuse (see `nativephp-clean` skill), secrets (never commit `.env`).
6. **Test** — `php artisan test --compact` green + true native precompile lint on touched Blade views (see `randevu` skill). Fix failures before pushing.
7. **Ship** — `git checkout -b issue-<n>-<slug>` **from main**, push, open PR with `Closes #n`. Then move to the next task only.

## Rules

- One task = one branch from `main` = one PR. Branches always start from `main`, never from another task branch.
- Never edit an `in-progress` issue owned by someone else.
- Labels: `ready` = unclaimed, `in-progress` = claimed.

## Open tracking

- Source of truth is live GitHub state (`gh issue list --state all`) — never trust a cached summary. Pick the next `ready` issue from there.
