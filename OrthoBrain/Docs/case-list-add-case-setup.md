# Case List + Add Case — local setup for teammates

Feature branch: `feature/case-list-and-add-case-integrated` (off `dev`).
Owner: Devansh (Prescription screen + persistence).

---

## TL;DR (copy-paste)

```bash
git fetch origin
git checkout feature/case-list-and-add-case-integrated

# PHP deps (see note on PHP version below)
composer install --ignore-platform-req=php

# Node deps (teammates' existing Vite pipeline is untouched)
npm install

# Fresh DB + seed (admin + doctor accounts). Adjust DB_DATABASE in .env first.
php artisan migrate:fresh --seed

# Expose storage/app/public via /storage/* URLs
php artisan storage:link

# Run
php artisan serve        # http://127.0.0.1:8000
# in a separate shell if you touch Vue/Tailwind: npm run dev
```

Log in as either:
- **Doctor** — `doctor@orthobrain.local` / `Password@1` → lands on `/dev/cases`
- **Admin**  — `admin@orthobrain.local` / `Password@1` → lands on `/admin`

---

## What's in this branch

- **`/dev/cases`** — Case List page (doctor scope). Replaces the old `/dev/cases/list` dashboard placeholder.
- **`/dev/cases/create`** + **`/dev/cases/{id}/edit`** — 10-section Add Case form (8 active + 2 placeholders). Scroll-spy rail, sticky action bar, 30s debounced autosave, Submit confirmation modal.
- **Prescription section wired to DB** end-to-end: `cases`, `prescriptions`, `prescription_tooth_restrictions`. Other 7 sections still write to `localStorage` with `// TODO:` markers for their respective owners.
- Shared UI helpers: crop modal (Cropper.js via CDN with Zoom In / Out / Rotate / Reset), reusable Web Speech mic on every `textarea[maxlength="5000"]`.

Routes added (under existing `auth`-guarded `/dev` group):

```
GET  /dev/cases                    doctor.cases.index
GET  /dev/cases/create             doctor.cases.create
POST /dev/cases                    doctor.cases.store
GET  /dev/cases/{id}/edit          doctor.cases.edit
POST /dev/cases/{id}/prescription  doctor.cases.prescription.update
POST /dev/cases/{id}/submit        doctor.cases.submit
```

---

## Gotchas

### 1. PHP 8.3 vs 8.4 (composer.lock)
`composer.lock` pins symfony 8.x which requires PHP 8.4. On PHP 8.3 you need `--ignore-platform-req=php` during install.

At runtime, PHP 8.3 boxes hit a fatal on `PUT / PATCH / DELETE` with JSON bodies (Symfony's `Request::createFromGlobals` calls the PHP-8.4-only `request_parse_body()`). Our Prescription save endpoint is therefore a `POST`, not a `PUT`. If you hit similar issues on your own endpoints on 8.3, prefer POST or install PHP 8.4.

### 2. Admin password
Default in `.env.example` is `ChangeMe@123` — this branch's local `.env` uses `Password@1` for both admin and doctor to keep dev logins simple. Adjust for your env.

### 3. Voice dictation
Uses Web Speech API. Works in Chrome / Edge. Firefox ships `SpeechRecognition` behind a flag — in Firefox the mic still appears (faded) and clicking it shows a toast saying it's unavailable.

### 4. Image uploads
Prototype still uses `URL.createObjectURL()` in browser memory for Photographs / X-Rays. Binaries disappear on refresh; metadata persists in draft. `storage:link` is ready for the real upload flow whenever we wire it to `storage/app/public/case-uploads/{caseId}/…`.

### 5. Cropper / heic2any
Loaded via CDN (jsdelivr) inside `resources/views/content/cases/add-case.blade.php`. Not added to `package.json` — no npm install step needed for the case feature.

---

## Smoke test (3 minutes)

1. Log in as doctor, you should land on `/dev/cases` → "No cases yet" + "+ New Case".
2. Click **+ New Case**. Browser URL is `/dev/cases/create`.
3. Fill Prescription section only (pick Arches, pick an IPR value, select 2 teeth in Tooth Movement Restrictions, type in Additional Comments). Wait 30s — top bar reads "Saved at HH:MM". URL should have rewritten itself to `/dev/cases/{id}/edit`.
4. Refresh the page. Prescription values should rehydrate from DB (not localStorage).
5. Click **Submit**. Confirmation modal → Confirm → redirects to `/dev/cases` with a "Case submitted" toast; row shows `SUBMITTED`.
6. Peek at DB:

```sql
SELECT * FROM cases;
SELECT * FROM prescriptions;
SELECT * FROM prescription_tooth_restrictions;
```

### DevTools self-check
On any `/dev/cases/create` or `/edit` load, the console prints one line:

```
[AddCase] deps check — Alpine=true Cropper=true heic2any=true bootstrap=true feather=true photographsSection=true
```

All `true` = vendor stack is healthy. Any `false` = stop, tell Devansh which one.

---

## What's NOT in this branch (owner / follow-up)

- Patient Information persistence — Rani/Kamlesh schema extension to `cases` or a `patients` table.
- Additional Information / Impressions / Shipping Address / Submit Order server endpoints — one-at-a-time swap with the matching section owner.
- Photographs / X-Rays S3 (or disk) upload endpoint. `storage:link` is prepared but no upload route yet.
- Perfect Smile Plan + Additional Records sections (still placeholders by spec).
- Doctor preferences settings page (Prescription defaults read from the `doctors` table enums; editing them is out of scope for this PR).

---

## If things look wrong

- Fresh `.env` issues → copy from `.env.example`, set `DB_DATABASE`, run `php artisan key:generate`.
- "Unauthenticated" on `POST /dev/cases` → session probably expired; refresh the page once.
- Tile modal buttons dead → check the `[AddCase] deps check` line in the console; most likely `bootstrap=false` or `Alpine=false`.
- Cropper modal blank → `Cropper=false` in the deps check. Check your network can reach `cdn.jsdelivr.net`.
