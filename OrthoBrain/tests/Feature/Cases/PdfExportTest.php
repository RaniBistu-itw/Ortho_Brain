<?php

// PDF export — verifies the doctor-side endpoint streams a PDF
// for both media-having and media-empty cases. We don't assert
// on PDF content (DomPDF output is binary); status + Content-Type
// are sufficient to confirm the route renders without throwing,
// which is the bug class this exercise the most.

use App\Support\ActivePractice;
use Illuminate\Support\Facades\DB;

it('generates a PDF for a case that has media', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    // Point at a real seeded image known to exist in storage/app/public.
    // The base64 builder in CasePdfController calls file_get_contents()
    // on storage_path('app/public/' . $path); the seed file ships with
    // the repo, so RefreshDatabase doesn't affect it.
    DB::table('case_media')->insert([
        'case_id'       => $caseId,
        'section'       => 'photograph',
        'tile_id'       => 'frontal-rest',
        'disk'          => 'public',
        'path'          => 'case-media/1/photograph/seed-frontal-rest.jpg',
        'mime_type'     => 'image/jpeg',
        'size_bytes'    => 34164,
        'original_name' => 'seed.jpg',
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/export.pdf");

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toStartWith('application/pdf');
});

it('generates a PDF for a case with no media', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/export.pdf");

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toStartWith('application/pdf');
});
