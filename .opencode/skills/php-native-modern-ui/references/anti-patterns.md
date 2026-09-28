# Native-PHP UI anti-patterns

Longer list of tells that make hand-rolled PHP sites look interchangeable, and the concrete fix.

| Tell | Why it happens in native PHP | Fix |
|---|---|---|
| Every page has its own `<head>` and nav, slightly different each time | No partial system, copy-paste between files | `require`d `header.php`/`footer.php`, one source of truth |
| Bootstrap CDN link, only default theme variables touched | Fastest way to get "something" styled | Either fully theme Bootstrap's CSS variables to the project's own palette, or drop it for plain CSS + tokens.css |
| `.card` used for literally every content block | Bootstrap/generic-kit default, easiest class to reach for | Use cards only where content is genuinely a discrete item in a collection; tables, lists, and plain sections for everything else |
| Inline `style="color: red;"` scattered in templates | Quick fix under time pressure | Move to a class in `components.css`; use `--color-danger` token |
| jQuery `$(document).ready` + a datepicker/lightbox plugin | Old tutorials/StackOverflow answers default to jQuery | Native `<input type="date">`, `<dialog>`, or a few lines of vanilla JS |
| Same blue (`#007bff`/`#0d6efd`) for links, buttons, and focus rings | Bootstrap default left untouched | Choose an accent deliberately in Step 2, and use it consistently but not as the *only* color decision made |
| Forms styled with browser defaults (small checkboxes, unstyled `<select>`) | No form styling pass done at all | Style native form elements directly with tokens; add `:focus-visible` states |
| All-caps tracked-out labels above every section ("OUR SERVICES") | Generic template chrome, common in page builders and AI output alike | Use plain sentence-case headings; reserve emphasis for where it's earned |
| No dark-mode / no `prefers-color-scheme` handling | Never added, low priority under deadline | Add token overrides under `@media (prefers-color-scheme: dark)` once tokens.css exists — cheap once tokens exist |
| Mixed units per file (`px` here, `em` there, magic numbers) | No spacing scale defined | Spacing scale in tokens.css (`--space-*`), reference everywhere |
| XSS via unescaped `echo $_GET['name']` or similar in templates | Fast to write, easy to forget | Always `htmlspecialchars()` / an `e()` helper around dynamic output, even in "just UI" work |
