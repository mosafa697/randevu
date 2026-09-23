# GH Issues Skill (claim protocol)

Prevents conflicts when multiple agents/sessions work this repo.

## Issues

- #1 Scaffold Laravel + NativePHP mobile (in-progress)
- #2 Randevu model + distance from today
- #3 Add form for new randevu
- #4 Follow UI for upcoming + memories
- #5 Edit + delete a randevu
- #6 Project skills + issue claim rules

## Claim before editing

```powershell
gh issue view <n>
gh issue edit <n> --add-assignee @me --remove-label ready --add-label in-progress
# re-check: gh issue view <n> — if assignee is not you, stop
```

## Work rules

- One issue = one branch `issue-<n>-<slug>` = one PR.
- Keep changes scoped to the issue's "Done when" list.
- Verify with `php artisan test --compact` before pushing.
- Close via PR body `Closes #n`, then move to next issue only.

## Labels

- `ready` = unclaimed, `in-progress` = claimed. Never edit an `in-progress` issue owned by someone else.
