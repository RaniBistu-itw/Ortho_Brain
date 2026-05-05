# Case List + Add Case — local setup for teammates

Owner: Devansh (Prescription screen + persistence).

**Branches on `origin`:**
- `feature/case-list-and-add-case` — clean feature work (no merge commits). Reviewable diff against `dev`.
- `feature/case-list-and-add-case-integrated` — **PR this one.** Above + `origin/dev` merged in, conflict resolved, smoke-tested on a fresh DB.

---

## 1. Pull + install

```bash
git fetch origin
git checkout feature/case-list-and-add-case-integrated
git pull --ff-only

# PHP deps. Platform-req bypass needed on PHP 8.3 (see troubleshooting).
composer install --ignore-platform-req=php

# Node deps — the existing Vite pipeline is untouched; no new packages.
npm install

# Configure .env (first time only)
cp .env.example .env
php artisan key:generate
# then set DB_DATABASE / DB_USERNAME / DB_PASSWORD to your local MariaDB

# Fresh DB + seed (creates admin + doctor accounts; safe to re-run)
php artisan migrate:fresh --seed

# Expose storage/app/public under /storage/* (one-time)
php artisan storage:link

# Run
php artisan serve        # http://127.0.0.1:8000
```

---

## 2. Accounts + smoke test (3 minutes)

| Role   | Email                       | Password    | Lands on    |
|--------|-----------------------------|-------------|-------------|
| Doctor | doctor@orthobrain.local     | Password@1  | `/dev/cases`|
| Admin  | admin@orthobrain.local      | Password@1  | `/admin`    |

1. Log in as doctor → "No cases yet" + **+ New Case** button.
2. Click **+ New Case** → `/dev/cases/create`.
3. Fill Prescription only: pick Arches, pick an IPR value, select 2 teeth in Tooth Movement Restrictions, type in Additional Comments.
4. Wait 30s (or click **Save Draft**). Top bar reads "Saved at HH:MM"; URL rewrites to `/dev/cases/{id}/edit`.
5. Refresh page → Prescription values rehydrate from DB.
6. Click **Submit** → Confirm → redirects to `/dev/cases` with toast; row shows `SUBMITTED`.

```sql
-- Peek at persisted state
SELECT * FROM cases;
SELECT * FROM prescriptions;
SELECT * FROM prescription_tooth_restrictions;
```

### DevTools self-check
On any `/dev/cases/create` or `/edit` load, the console prints exactly one line:

```
[AddCase] deps check — Alpine=true Cropper=true heic2any=true bootstrap=true feather=true photographsSection=true
```

All `true` = vendor stack is healthy. Any `false` = stop, tell Devansh which one.

---

## 3. What's in vs what's out

### In this branch
- **Routes** under existing `/dev` auth group:
  ```
  GET  /dev/cases                    doctor.cases.index
  GET  /dev/cases/create             doctor.cases.create
  POST /dev/cases                    doctor.cases.store
  GET  /dev/cases/{id}/edit          doctor.cases.edit
  POST /dev/cases/{id}/prescription  doctor.cases.prescription.update
  POST /dev/cases/{id}/submit        doctor.cases.submit
  ```
- **Schema** — 3 new tables: `cases`, `prescriptions`, `prescription_tooth_restrictions`.
- **Models** — `CaseModel` (table `cases`; class avoids PHP `case` keyword), `Prescription`, `PrescriptionToothRestriction`. `Doctor::cases()` relation added.
- **UI** — 10-section Add Case form (8 active, 2 placeholders), scroll-spy rail, sticky top bar, 30s debounced autosave, Submit confirmation modal, "Cases" item in the doctor sidebar.
- **Reusable helpers** — crop modal with Zoom In / Out / Rotate / Reset (Cropper.js v1 via CDN), Web Speech mic auto-attached to every `textarea[maxlength="5000"]`.
- **Only the Prescription slice persists to DB.** Other 7 sections still write to `localStorage` behind `// TODO:` markers for their owners.

### Not in this branch (owner pickups)
- Patient Information persistence — needs schema extension (patients table or columns on `cases`).
- Additional Information / Impressions / Shipping Address / Submit Order server endpoints — one-at-a-time swap.
- Photographs / X-Rays disk upload endpoint. `storage:link` is set up, but no route yet.
  **Reuse `app/Services/ImageUploadService.php`** (shipped in PR #14, `feature/local-image-save`) rather than building a parallel service.
- Perfect Smile Plan + Additional Records sections (still placeholders by spec).
- Doctor preferences settings page (Prescription defaults read from existing `doctors` enums; editing UI is out of scope).

---

## 4. Keeping your local branch fresh

### `dev` moved and I want the latest here

```bash
git fetch origin
git checkout feature/case-list-and-add-case-integrated
git merge origin/dev
# If a conflict surfaces, resolve it, then:
git commit                          # completes the merge
```

Use `merge` (not `rebase`) — the integration branch is shared via PR; rebasing rewrites history and breaks review threads.

### I pushed, then `dev` moved, my push is rejected

```bash
git fetch origin
git merge origin/dev                # resolve any conflicts
php artisan migrate:fresh --seed    # re-verify everything still runs
git push origin feature/case-list-and-add-case-integrated
```

### I only want to peek at what changed upstream

```bash
git fetch origin
git log --oneline HEAD..origin/dev          # commits I'm missing
git diff --stat HEAD..origin/dev            # file-level scope
git merge --no-commit --no-ff origin/dev    # dry-run
git merge --abort                           # back out
```

---

## 5. Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| `composer install` fails on symfony/laravel packages requiring PHP 8.4 | composer.lock pins symfony 8 | Use `composer install --ignore-platform-req=php`, OR install PHP 8.4 |
| `PUT` / `PATCH` / `DELETE` with JSON body 500s with `Call to undefined function request_parse_body()` | Symfony 8 calls the PHP-8.4-only `request_parse_body()` | Use `POST` instead (our Prescription save already is), OR install PHP 8.4 |
| `POST /dev/cases` returns 401 "Unauthenticated" | Session expired | Refresh the page once |
| Tile modal buttons dead (Replace / Remove / Crop) | Alpine or bootstrap failed to load | Open DevTools → check the `[AddCase] deps check` line; the `false` field is the broken one |
| Crop modal opens but image doesn't appear / buttons do nothing | `Cropper=false` in deps check | Network can't reach `cdn.jsdelivr.net`, OR a previous tab cached the old `crop-modal.js`. Hard-refresh (Ctrl+Shift+R) |
| Mic button absent on textareas in Chrome | Script blocked, or fresh fetch needed | Hard-refresh once; check `[VoiceInput] refresh — found=8` in console |
| Mic present but clicking shows a toast | Browser isn't Chrome/Edge (Web Speech unsupported) | Expected — no action needed |
| `PracticesSeeder skipped: no ACTIVE zipcodes found` during seed | Teammates' `PracticesSeeder` depends on geo sample data not yet in a dedicated seeder | Harmless; import teammates' `database/seeders/sql/sample_data.sql` if you want `practices` populated |
| `migrate:fresh` wipes sessions — existing browser tab 401s | Session table recreated | Log out + log back in |
| `.env` missing / `APP_KEY` error | Fresh clone didn't copy `.env.example` | `cp .env.example .env && php artisan key:generate` |
