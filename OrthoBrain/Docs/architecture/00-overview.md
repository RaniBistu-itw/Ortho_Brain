> _Last verified: against origin/dev @ `096aea5` on 2026-05-06. If editing, update this stamp._

# OrthoBrain Architecture Docs

This folder is the *describes-the-system* layer: what's here, how it
fits together, and where to look. It is deliberately concise and
linked — each page is a map, not a textbook.

For *conventions and gotchas* (rules earned from real bugs), see
[CLAUDE.md](../../CLAUDE.md). The two layers are complementary:

| | What it answers | When to update |
|---|---|---|
| `CLAUDE.md` | "What rule do I have to follow?" | A bug surfaces a generalisable rule |
| `Docs/architecture/` | "Where does X live and how does it flow?" | A feature changes the system map |

The split is intentional. Don't put gotchas here; don't put system maps
in CLAUDE.md.

## Index

| # | Doc | What it covers |
|---|---|---|
| 01 | [Tech stack](01-tech-stack.md) | Laravel 13, PHP 8.3+, Livewire 4, Vite, Tailwind 4, Bootstrap Vuexy, Alpine, dompdf — versions, why each, where loaded |
| 02 | [Request lifecycle](02-request-lifecycle.md) | Route → middleware → controller → view; the `users` ↔ `doctors`/`admins` identity model; rate limiters; exception rendering |
| 03 | [Database schema](03-database-schema.md) | All 43 migrations grouped (auth, geo, products, doctors, cases, prescriptions, practices, AI, notifications); FK map; persistence-debt callouts |
| 04 | [Add Case flow](04-add-case-flow.md) | The central feature: 10 sections, partials + JS mirror, Form Requests, Alpine sync pattern, PDF export |
| 05 | [Frontend pipeline](05-frontend-pipeline.md) | `public/js/` ↔ `resources/js/` mirror, what Vite builds vs hand-loaded assets, cache-busting rule, CDN vendors |
| 06 | [Admin parity](06-admin-parity.md) | Doctor↔admin parallel controllers, Reflection debt, audit checklist, `$adminMode` shared blades |
| 07 | [AI services](07-ai-services.md) | `app/Services/AI/` provider chain (Gemini → Ollama → Canned), DTOs, contracts, smile preview + image analysis controllers |
| 08 | [Testing & quality gates](08-testing-and-quality-gates.md) | Pest layout, manual smoke-test discipline, validation-feedback discipline, pre-commit skill |

## Existing docs (not duplicated here)

These are still authoritative; the architecture docs cross-reference
them rather than restate them.

- [Docs/api.md](../api.md) — endpoint contracts (request/response shapes)
- [Docs/dev-commands.md](../dev-commands.md) — day-to-day shell commands
- [Docs/case-list-add-case-setup.md](../case-list-add-case-setup.md) — local-setup walkthrough
- [Docs/photo-picker-changelog.md](../photo-picker-changelog.md) — photo picker history
- [Docs/prompt-results.md](../prompt-results.md) — prompt-engineering log
- `docs/superpowers/specs/` (lowercase `docs/`, separate from this `Docs/` tree) — design specs

## Updating these docs

Use the `update-architecture-docs` skill (user-level, at
`~/.claude/skills/update-architecture-docs/`). Trigger phrases: "doc
the X feature", "I just shipped Y, update the docs", "after merge
update architecture docs". The skill will pick the right file, append
additively, and ask whether the change belongs in CLAUDE.md instead
when it's a gotcha rather than a system-map change.

The skill is per-developer (lives in `~/.claude/skills/`), not in this
repo — teammates who want it can either symlink or copy
`update-architecture-docs/SKILL.md` to their own `~/.claude/skills/`.
