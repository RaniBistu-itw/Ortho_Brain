<?php

use App\Models\Prescription;
use App\Services\AI\PromptBuilder;

beforeEach(function () {
    $this->builder = new PromptBuilder();
});

it('produces a prompt with the hard-constraint clauses even for a null prescription', function () {
    $prompt = $this->builder->forSmilePreview(null);

    expect($prompt)->toContain('HARD CONSTRAINTS');
    expect($prompt)->toContain('Modify ONLY the visible teeth');
    expect($prompt)->toContain('No specific prescription on file');
});

it('renders every populated prescription field as a plan bullet', function () {
    $p = new Prescription([
        'case_id' => 1,
        'arches'               => 'BOTH',
        'ipr_enabled'          => true,
        'ipr_value'            => 'DEFER',
        'extractions_enabled'  => true,
        'extractions_value'    => 'NO',
        'attachments_enabled'  => true,
        'attachments_value'    => 'STEP_1',
        'elastics_enabled'     => true,
        'elastics_value'       => 'YES',
    ]);
    // Stub the restriction relations so PromptBuilder's ->get() returns empty collections.
    $p->setRelation('movementRestrictions', collect());
    $p->setRelation('attachmentRestrictions', collect());

    $prompt = $this->builder->forSmilePreview($p);

    expect($prompt)->toContain('Arches to treat: both arches');
    expect($prompt)->toContain('IPR protocol: defer IPR decision');
    expect($prompt)->toContain('Extractions planned: no');
    expect($prompt)->toContain('Attachments: starting at step 1');
    expect($prompt)->toContain('Elastics: yes');
    expect($prompt)->not->toContain('Do NOT move these teeth');
});

it('adds the preserve-maxillary clause for mandibular-only arches', function () {
    $p = new Prescription(['case_id' => 1, 'arches' => 'MANDIBULAR']);
    $p->setRelation('movementRestrictions', collect());
    $p->setRelation('attachmentRestrictions', collect());

    $prompt = $this->builder->forSmilePreview($p);

    expect($prompt)->toContain('mandibular (lower) only');
    expect($prompt)->toContain('keep the maxillary (upper) teeth IDENTICAL');
});

it('omits IPR/attachments lines when their enabled flag is false', function () {
    $p = new Prescription([
        'case_id' => 1,
        'arches'              => 'BOTH',
        'ipr_enabled'         => false,
        'ipr_value'           => 'NO_IPR',
        'attachments_enabled' => false,
        'attachments_value'   => 'STEP_1',
        'elastics_enabled'    => false,
    ]);
    $p->setRelation('movementRestrictions', collect());
    $p->setRelation('attachmentRestrictions', collect());

    $prompt = $this->builder->forSmilePreview($p);

    expect($prompt)->not->toContain('IPR protocol:');
    expect($prompt)->not->toContain('Attachments:');
    expect($prompt)->not->toContain('Elastics:');
});

it('is deterministic for identical inputs', function () {
    $make = fn () => tap(
        new Prescription(['case_id' => 1, 'arches' => 'MAXILLARY', 'ipr_enabled' => true, 'ipr_value' => 'DEFER']),
        function ($p) {
            $p->setRelation('movementRestrictions', collect());
            $p->setRelation('attachmentRestrictions', collect());
        }
    );

    $a = $this->builder->forSmilePreview($make());
    $b = $this->builder->forSmilePreview($make());
    expect($a)->toBe($b);
});
