<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\Doctor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CasePdfController extends Controller
{
    /**
     * Stream a section-wise PDF report for a case.
     *
     * Photographs and X-rays are embedded as base64 data URIs (built below
     * from `case_media` rows on disk). Sections that don't have a server-side
     * model (e.g., draft submit-order metadata) surface whatever the client
     * passed in `addCaseState`, falling back to "Not yet captured."
     *
     * Doctor scope: case must belong to the authenticated doctor's active
     * practice. Admin scope is wide-open (any case).
     */
    public function export(Request $request, int $id)
    {
        $isAdmin = $request->boolean('admin') || str_starts_with($request->path(), 'admin/');

        $query = CaseModel::with([
            'doctor.practice',
            'practice.zipcode',
            'practice.city',
            'practice.state',
            'practice.country',
            'prescription.toothRestrictions',
            'media' => function ($q) {
                // Load photograph and xray tiles only for PDF embed.
                // Ordered for consistent grid layout across renders.
                $q->whereIn('section', ['photograph', 'xray'])
                  ->orderBy('section')
                  ->orderBy('tile_id');
            },
        ]);

        if (! $isAdmin) {
            $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();
            $practiceId = currentPractice()?->id;
            $query->where('doctor_id', $doctor->id)
                  ->when($practiceId, fn ($q) => $q->where('practice_id', $practiceId));
        }

        $case = $query->findOrFail($id);

        // Build base64 data URIs for DomPDF.
        // DomPDF cannot reliably fetch HTTP URLs or filesystem paths
        // without isRemoteEnabled=true (disabled by default).
        // Base64 embeds image data directly in HTML — works in any
        // DomPDF config. ~30KB per image, ~270KB for 9 photos.
        $mediaBySection = $case->media
            ->groupBy('section')
            ->map(function ($items) {
                return $items->map(function ($m) {
                    $filePath = storage_path('app/public/' . $m->path);
                    if (! file_exists($filePath)) {
                        return null;
                    }
                    return [
                        'tile_id'  => $m->tile_id,
                        'data_uri' => 'data:' . $m->mime_type
                                      . ';base64,'
                                      . base64_encode(
                                          file_get_contents($filePath)
                                        ),
                    ];
                })->filter()->values();
            });

        // Client-side draft state (patient info, shipping, additional info,
        // smile plan, impressions, submit-order). The form posts whatever lives
        // in window.AddCaseState alongside the request.
        $clientState = $request->input('addCaseState') ?: [];
        if (is_string($clientState)) {
            $clientState = json_decode($clientState, true) ?: [];
        }

        $pdf = Pdf::loadView('content.cases.pdf.case-report', [
            'case'           => $case,
            'clientState'    => $clientState,
            'generatedAt'    => now(),
            'isAdmin'        => $isAdmin,
            'mediaBySection' => $mediaBySection,
        ])->setPaper('a4', 'portrait');

        $filename = 'case-' . ($case->case_code ?: $case->id) . '.pdf';

        return $pdf->stream($filename);
    }
}
