<?php

use App\Models\CaseMedia;
use App\Support\ActivePractice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// All routes use POST (not PUT/DELETE) — see CaseMediaController routes
// in routes/web.php and the PHP 8.3 + Symfony 8 fatal note in
// case-media-api.js.
//
// Accept: application/json header is required so that Laravel's `$request->
// validate(...)` failures return 422 with JSON `errors` instead of redirecting
// 302 with session errors. This is what the JS client sets in case-media-api.js.
function uploadMedia(int $pid, int $caseId, UploadedFile $file, string $tile = 'profile'): \Illuminate\Testing\TestResponse
{
    return test()->withSession([ActivePractice::SESSION_KEY => $pid])
        ->withHeaders(['Accept' => 'application/json'])
        ->post("/dev/cases/{$caseId}/media/upload", [
            'section' => 'photograph',
            'tile_id' => $tile,
            'file'    => $file,
        ]);
}

beforeEach(function () {
    Storage::fake('public');
});

// ─── Happy path ──────────────────────────────────────────────────────────────

it('accepts a valid 1MB JPG to a photograph tile', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();
    $file = UploadedFile::fake()->image('ok.jpg', 800, 600)->size(1024); // 1 MB

    uploadMedia($pid, $caseId, $file)
        ->assertStatus(200)
        ->assertJson(['ok' => true, 'section' => 'photograph', 'tile_id' => 'profile']);

    expect(CaseMedia::where('case_id', $caseId)->where('tile_id', 'profile')->exists())->toBeTrue();
});

it('accepts a valid PNG', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();
    $file = UploadedFile::fake()->image('ok.png', 800, 600)->size(500);

    uploadMedia($pid, $caseId, $file, 'frontal-rest')->assertStatus(200);
});

// HEIC: not feasible to test here. UploadedFile::fake()->image() can't
// synthesize HEIC content (heif/heic libraries aren't part of the test
// stack), and createWithContent() reports MIME from the extension rather
// than sniffing content (see the rename note below). HEIC acceptance is
// covered by the manual smoke test instead, and by the controller's
// ALLOWED_MIME constant including 'image/heic' / 'image/heif'.

// ─── MIME rejection (Finding #1 — server allowlist) ──────────────────────────
//
// Note on rename-attack coverage: the original audit finding was about a user
// renaming `.txt` to `.jpg` and the file slipping through. UploadedFile::fake()
// reports MIME from the file *extension*, not by sniffing content (verified by
// having `createWithContent('fake.jpg', 'plain text')` return image/jpeg). So
// Pest cannot test the rename-then-sniff path — production's `getMimeType()`
// reads the file system, which the fake doesn't fully simulate.
//
// What we CAN test: the controller's MIME allowlist rejects non-image MIMEs.
// We do that by using a non-image extension. The rename-attack path itself is
// covered by the manual smoke test (Rejection mode 1 — rename .txt → .jpg).

it('rejects a .txt file (non-image MIME) via server allowlist', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();
    $file = UploadedFile::fake()->createWithContent('notes.txt', 'this is plain text not an image');

    uploadMedia($pid, $caseId, $file)->assertStatus(422);
    expect(CaseMedia::where('case_id', $caseId)->exists())->toBeFalse();
});

it('rejects a .pdf file (non-image MIME) via server allowlist', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();
    $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

    uploadMedia($pid, $caseId, $file)->assertStatus(422);
});

// ─── Edge: missing/empty file ────────────────────────────────────────────────

it('rejects a request with no file at all', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    test()->withSession([ActivePractice::SESSION_KEY => $pid])
        ->withHeaders(['Accept' => 'application/json'])
        ->post("/dev/cases/{$caseId}/media/upload", [
            'section' => 'photograph',
            'tile_id' => 'profile',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

// ─── Size cap (Finding #7 — 5MB alignment) ───────────────────────────────────

it('accepts a 5MB JPG at the cap', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();
    // size() is in KB. 5MB = 5120 KB — exactly at the validate rule's max.
    $file = UploadedFile::fake()->image('atcap.jpg', 1200, 900)->size(5120);

    uploadMedia($pid, $caseId, $file)->assertStatus(200);
});

it('rejects a 5.5MB JPG over the cap with 422 (NOT 413)', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();
    // Laravel's max: rule produces 422 with errors.file (NOT 413). Genuine 413
    // only fires from proxy/PHP server-level limits — different path entirely.
    // The JS helper's upgradeUploadError() sniffs body.errors.file for size-
    // related copy so this surfaces as "File too large" not "not supported".
    $file = UploadedFile::fake()->image('over.jpg', 1200, 900)->size(5632); // 5.5 MB

    uploadMedia($pid, $caseId, $file)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

it('rejects a 6MB JPG well over the cap', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();
    $file = UploadedFile::fake()->image('big.jpg', 1600, 1200)->size(6144); // 6 MB

    uploadMedia($pid, $caseId, $file)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});
