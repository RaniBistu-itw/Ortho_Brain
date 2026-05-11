<?php

namespace App\Http\Controllers\Admin;

use App\Models\CaseModel;
use App\Notifications\CaseEditedByAdminNotification;
use Illuminate\Http\Request;

/**
 * Admin variant of the media upload/destroy/reorder controller.
 * Admins are not scoped by doctor_id or practice_id — they have
 * full visibility over all cases.
 */
class CaseMediaController extends \App\Http\Controllers\CaseMediaController
{
    protected function resolveCaseForDoctor(int $caseId): CaseModel
    {
        return CaseModel::findOrFail($caseId);
    }

    // Admin can upload/destroy/reorder media on any non-DRAFT case.
    // The parent guard (abortIfNotDraft) is a doctor-side constraint only.
    // APPROVED/REJECTED are read-only client-side via window.__isReadOnly.
    protected function abortIfNotDraft(CaseModel $case): void {}

    // Override to dispatch edit notification after admin media upload.
    public function upload(Request $request, int $caseId)
    {
        $response = parent::upload($request, $caseId);
        // Notify doctor of admin media change. If upload threw, we never reach here.
        $case = CaseModel::find($caseId);
        $case?->loadMissing('doctor.user');
        $case?->doctor?->user?->notify(
            new CaseEditedByAdminNotification($case, 'media')
        );
        return $response;
    }

    // Override to dispatch edit notification after admin media destroy.
    // Signature matches parent: no Request param on destroy.
    public function destroy(int $caseId, string $section, string $tileId)
    {
        $response = parent::destroy($caseId, $section, $tileId);
        // Notify doctor of admin media change. If destroy threw, we never reach here.
        $case = CaseModel::find($caseId);
        $case?->loadMissing('doctor.user');
        $case?->doctor?->user?->notify(
            new CaseEditedByAdminNotification($case, 'media')
        );
        return $response;
    }

    // Override to dispatch edit notification after admin media reorder.
    public function reorder(Request $request, int $caseId)
    {
        $response = parent::reorder($request, $caseId);
        // Notify doctor of admin media change. If reorder threw, we never reach here.
        $case = CaseModel::find($caseId);
        $case?->loadMissing('doctor.user');
        $case?->doctor?->user?->notify(
            new CaseEditedByAdminNotification($case, 'media')
        );
        return $response;
    }
}
