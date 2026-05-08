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
}
