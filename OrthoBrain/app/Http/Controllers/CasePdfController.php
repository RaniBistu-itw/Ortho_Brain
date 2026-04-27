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
     * Photographs / X-rays are intentionally excluded from v1 — those blobs
     * live in IndexedDB only today (no DB persistence). Each section in the
     * PDF that doesn't have a server-side model surfaces whatever the client
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
        ]);

        if (! $isAdmin) {
            $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();
            $practiceId = currentPractice()?->id;
            $query->where('doctor_id', $doctor->id)
                  ->when($practiceId, fn ($q) => $q->where('practice_id', $practiceId));
        }

        $case = $query->findOrFail($id);

        // Client-side draft state (patient info, shipping, additional info,
        // smile plan, impressions, submit-order). The form posts whatever lives
        // in window.AddCaseState alongside the request.
        $clientState = $request->input('addCaseState') ?: [];
        if (is_string($clientState)) {
            $clientState = json_decode($clientState, true) ?: [];
        }

        $pdf = Pdf::loadView('content.cases.pdf.case-report', [
            'case'        => $case,
            'clientState' => $clientState,
            'generatedAt' => now(),
            'isAdmin'     => $isAdmin,
        ])->setPaper('a4', 'portrait');

        $filename = 'case-' . ($case->case_code ?: $case->id) . '.pdf';

        return $pdf->stream($filename);
    }
}
