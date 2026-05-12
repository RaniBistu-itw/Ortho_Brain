# OrthoBrain — Useful Commands

A reference for the commands and skills integrated into the OrthoBrain dev
workflow. Pair with [CLAUDE.md](CLAUDE.md) for conventions and gotchas.

---

## 1. Claude Code skills (slash commands)

Invoke with `/<skill-name>` in Claude Code. All skills live under
`~/.claude/skills/`.

| Skill | Purpose | Example |
|---|---|---|
| `/restart-dev-server` | Kill any orphan `php artisan serve` on the chosen port, restart it in the background, verify HTTP 200 on `/login`. | `/restart-dev-server 8001` |
| `/switch-branch` | Switch between `main` (Phase 1-7 prototype) and `dev` (team canonical). Handles `.env` swap, cache clear, asset rebuild, DB pointer flip. | `/switch-branch dev` |
| `/post-pull` | Run after `git pull` / `git merge origin/dev`: apply migrations, regenerate composer autoloader (bypassing the PHP 8.4 platform check on this 8.3 box), clear all Laravel caches, reinstall node deps only if `package-lock.json` changed. | `/post-pull` |
| `/seed-geo` | Populate `ob1` with countries/states/cities/zipcodes + product/scanner masters from `database/seeders/sql/sample_data.sql`, then re-run `PracticesSeeder`. Use when "no zipcodes" or PracticesSeeder skipped. | `/seed-geo` |
| `/pre-commit-checks` | Cache clear + frontend build + smoke HTTP on `/login` + Pint + Pest (if available). Run before committing. | `/pre-commit-checks` |
| `/add-case-section-scaffold` | Generate boilerplate for a new Add Case form section (Blade partial, Alpine-aware JS, SCSS, Form Request, rail-item registration). | `/add-case-section-scaffold smile-plan` |
| `/update-architecture-docs` | Append a dated "Recent changes" entry to the right doc(s) under `Docs/architecture/`. Routes gotchas/conventions to CLAUDE.md instead. | `/update-architecture-docs` |
| `/phase-runner` | Detect the highest completed phase, open the next `Docs/Prompts/phase-N-*.md`, start a fresh todo list. | `/phase-runner` or `/phase-runner 8` |

---

## 2. Laravel / Artisan

```bash
php artisan serve --port=8001           # start dev server (use /restart-dev-server in Claude)
php artisan migrate                     # apply pending migrations
php artisan migrate:fresh --seed        # wipe + reseed (warns if geo data missing → run /seed-geo)
php artisan db:seed --class=PracticesSeeder
php artisan optimize:clear              # clear config/route/view/cache
php artisan route:list                  # inspect routes (use --path= to filter)
php artisan tinker                      # REPL against the running app
php artisan test                        # run Pest test suite
php artisan test --filter=SubmitGate    # run a subset
```

### Composer

```bash
composer install --ignore-platform-req=php   # local box is PHP 8.3, team requires 8.4
composer dump-autoload --ignore-platform-req=php
```

---

## 3. Frontend / Vite

```bash
npm run dev          # Vite dev server with HMR (for resources/ workflow)
npm run build        # production build → public/build/
```

Note: `public/js/scripts/cases/` and `public/css/` are NOT Vite outputs —
they are hand-managed assets loaded directly by Blade `<script>` /
`<link>` tags. Vite outputs to `public/build/` only.

---

## 4. Mirror discipline ([CLAUDE.md Entry 3](CLAUDE.md))

After editing any cases JS file, both copies must match:

```bash
diff -rq resources/js/scripts/cases/ public/js/scripts/cases/
# Expected: empty output. If non-empty, sync before pushing.
```

Quick sync (resources → public):

```bash
cp resources/js/scripts/cases/<file>.js public/js/scripts/cases/<file>.js
```

---

## 5. Cache-bust public/ assets ([memory: feedback_cache_bust_public_assets](~/.claude/projects/-home-admin1-Ortho-Brain/memory/feedback_cache_bust_public_assets.md))

Every Blade `<script>` / `<link>` referencing `public/js/**` or
`public/css/**` must carry a `?v={filemtime}` query string, e.g.:

```blade
<script src="{{ asset('js/scripts/cases/voice-input.js') }}?v={{ @filemtime(public_path('js/scripts/cases/voice-input.js')) ?: time() }}"></script>
```

Without it, edits ship invisibly because the browser keeps the cached
copy.

---

## 6. Local dev environment

```bash
ps aux | grep "artisan serve" | grep -v grep   # see when each server started
lsof -i :8001 | grep LISTEN                    # find PID holding a port
kill <PID>                                     # stop it (or use /restart-dev-server)
```

DB credentials (local `ob1`): root user, **empty password**.

```bash
mysql -u root ob1                               # quick DB shell
```

---

## 7. Git workflow ([memory: feedback_team_merge_flow](~/.claude/projects/-home-admin1-Ortho-Brain/memory/feedback_team_merge_flow.md))

```bash
git fetch origin                                # ALWAYS fetch first
git checkout -b feat/<topic> origin/dev         # branch from origin/dev, not local main
git push -u origin feat/<topic>
```

Conventional commit prefixes: `feat:`, `fix:`, `chore:`, `docs:`,
`test:`, `refactor:`. Split unrelated changes across separate PRs;
delete branches both sides after merge.

PR creation via GitHub CLI:

```bash
gh pr create --base dev --title "fix: <one-line>" --body-file pr-body.md
```

---

## 8. Pre-flight before commit

Recommended sequence ([CLAUDE.md Entry 9](CLAUDE.md)):

1. `/pre-commit-checks` — cache clear + build + smoke + lint + tests
2. `diff -rq resources/js/scripts/cases/ public/js/scripts/cases/`
3. Manual smoke test the affected flow in a real browser
4. Document the smoke test results in the PR description

---

## 9. Common gotchas (quick recall)

- **Auth::id() ≠ doctor.id** — use `$this->currentDoctor()->id` ([CLAUDE.md Entry 1](CLAUDE.md))
- **Doctor + Admin controller parity** — touch both ([CLAUDE.md Entry 2](CLAUDE.md))
- **Stale `php artisan serve`** — restart after every pull / branch switch ([CLAUDE.md Entry 7](CLAUDE.md))
- **Alpine `x-model` + `x-for` race** — wrap assignments in `$nextTick` ([CLAUDE.md Entry 6](CLAUDE.md))
- **Media swap with URL-only tiles** — never destroy+upload; use the reorder endpoint or hydrate blob first ([CLAUDE.md Entry 11](CLAUDE.md))
- **PHP 8.4 vs local 8.3** — pass `--ignore-platform-req=php` to composer ([memory: project_php_version_gotcha](~/.claude/projects/-home-admin1-Ortho-Brain/memory/project_php_version_gotcha.md))
