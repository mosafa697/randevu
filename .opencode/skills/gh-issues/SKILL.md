---
name: gh-issues
description: Use for this repo's task lifecycle — refine → plan → claim → implement → review → test → branch from main + PR, one task at a time. Load when starting/claiming a GitHub issue or when the user says "implementation", "commit", "ship".
---

# GH Issues Skill (task-by-task workflow)

One task at a time, end to end. Never work two tasks together.
Every step gate waits for the user's word — never jump ahead.

## Agreed flow (the user drives, step by step)

1. **User tells the task/bug** → I refine it and create a GH issue for it. Then STOP and wait.
2. **User says "implementation"** → I implement on the claimed branch. NEVER commit, push, or open a PR in this step. Then STOP and wait.
3. **User asks to commit** → I commit on a separate branch from `main`, push, and open the PR. Then STOP and wait for the next task.

## Task lifecycle (within the steps above)

1. **Refinement** — read `gh issue view <n>`, confirm the "Done when" list is clear and small. If vague, ask the user before coding.
2. **Planning** — short plan: files to touch, tests to add, how to verify. Keep it in the todo list.
3. **Claim** — assign yourself and move `ready` → `in-progress` BEFORE editing:
   ```powershell
   gh issue edit <n> --add-assignee "@me" --add-label "in-progress" --remove-label "ready"
   # NOTE (PowerShell): quote the label values or the CLI misparses them.
   gh issue view <n>  # re-check: if assignee is not you, stop
   ```
4. **Implement** — scoped to the issue's "Done when" only. No commit, no push, no PR — ever in this step.
5. **Review** — read-only pass with the `review` skill (`.opencode/skills/review/SKILL.md`): diff vs `main`, NativePHP clean-code + domain checklist, re-run tests/precompile evidence, findings by severity. Fix Blockers/Majors before Test/Ship.
6. **Test** — `php artisan test --compact` green + true native precompile lint on touched Blade views (see `randevu` skill). Fix failures before pushing.
7. **Ship (only on explicit user approval)** — `git checkout -b issue-<n>-<slug>` **from main**, commit, push, open PR with `Closes #n`. Then move to the next task only when told.

## Rules

- One task = one branch from `main` = one PR. Branches always start from `main`, never from another task branch.
- NEVER commit, push, or open a PR without explicit user sign-off — even if work is tested and green. Wait for approval at each gated step.
- Never edit an `in-progress` issue owned by someone else.
- Review is read-only — the `review` skill never edits or commits; ship only after a PASS verdict + explicit user sign-off.
- Labels: `ready` = unclaimed, `in-progress` = claimed.

## Open tracking

- Source of truth is live GitHub state (`gh issue list --state all`) — never trust a cached summary. Pick the next `ready` issue from there.
