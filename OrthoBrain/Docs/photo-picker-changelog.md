# Photo / X-Ray Picker — Change Log

## Current state

As of 2026-04-29: bare `_openFilePicker` (no JS guards). Synthetic-click bubble suppressed by `@click.stop` on the file input in `media-tile.blade.php`. Pending field-test verification in Chrome + Firefox on Linux.

## Change log (newest first)

| Date | PR | Touched | What it changed | Symptom it claimed to fix | Outcome |
|------|----|---------|-----------------|---------------------------|---------|
| 2026-04-29 | (this PR) | photographs.js, xrays.js, public/js mirrors | Reverted Guards 1+2+3 in `_openFilePicker` and bulk equivalents; removed all `_picker*` / `_lastChange*` / `_lastPicker*` / `_lastBulk*` state. Kept `@click.stop` from PR #72. | Mixed-symptom regression caused by stacked guards interacting non-deterministically | Pending field test (PHOTO-1..7) |
| 2026-04-28 | #78 | photographs.js, xrays.js | Added per-tile 800ms `_lastChangeByTile` recency guard | "Chrome+Linux phantom change event" | Reverted in this PR — bug also reported in Firefox; killed Replace-within-800ms |
| 2026-04-28 | #75 | photographs.js, xrays.js | Added per-tile `_pickerLockedTile` (60s timeout); reverted #72's input.value reorder | "Second dialog still appears" | Reverted in this PR — 60s lock + browsers fire no cancel event = user trapped on Cancel-then-reclick |
| 2026-04-28 | #72 | photographs.js, xrays.js, media-tile.blade.php | Added `@click.stop` on file input; 250ms `_lastPickerOpenAt` global guard; moved `input.value = ''` before `_processFile` | Synthetic click bubble after CSS swap (display:none → visually-hidden) | `@click.stop` correct, kept; 250ms guard reverted in this PR; input.value reorder already reverted in #75 |
| 2026-04-27 | #64 | photographs.js | (no picker change) Added server-side persistence | — | Last known clean baseline (SHA `a4b107f`) |
| 2026-04-24 | #45 | photographs.js | (no picker change) Added AI photo QC + Smile Plan | — | Picker still working |
| 2026-04-24 | (commit 672aa11) | photographs.js | (no picker change) 5MB cap + undo toast | — | Picker still working |
| 2026-04-21 | #61 | photographs.js (NEW) | Initial scaffolding | — | Initial picker shape |

## Rules

- One row per PR that touches `_openFilePicker`, `onFileInputChange`, `openBulkPicker`, `onBulkInputChange`, the file input markup, or any `_pickerLocked*` / `_lastChange*` / `_lastPickerOpenAt` / `_lastBulk*` state.
- "Outcome" must be filled in within 7 days — either "verified working" or "reverted in #N".
- If a fix is later removed, **do not delete its row** — annotate the Outcome cell.
