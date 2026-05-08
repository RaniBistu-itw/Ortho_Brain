<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CaseModel;

trait GuardsCaseStatus
{
    protected function abortIfNotDraft(CaseModel $case): void
    {
        abort_if(
            $case->status !== 'DRAFT',
            403,
            'This case has been submitted and cannot be edited.'
        );
    }
}
