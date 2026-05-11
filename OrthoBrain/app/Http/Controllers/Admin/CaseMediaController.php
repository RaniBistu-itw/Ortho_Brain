<?php

namespace App\Http\Controllers\Admin;

use App\Models\CaseModel;

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
}
