<?php

namespace App\Services\AI;

use App\Models\Prescription;

// Deterministic prompt construction. Identical prescription input ⇒ identical
// prompt string, so future caching by prompt hash becomes trivial. Keep this
// class side-effect-free — it's heavily unit-tested.
class PromptBuilder
{
    public function forSmilePreview(?Prescription $prescription): string
    {
        $plan = $this->planLines($prescription);
        $mandibularOnly = $prescription && strtoupper((string) $prescription->arches) === 'MANDIBULAR';

        $lines = [
            'Task: You are shown an intra-oral frontal smile photograph of a patient.',
            'Produce an edited version that visualises the likely post-treatment outcome',
            'for the orthodontic plan described below.',
            '',
            'HARD CONSTRAINTS:',
            "- Keep the person's face, skin tone, lip shape, lighting, pose, background, and image composition IDENTICAL.",
            '- Modify ONLY the visible teeth.',
            '- Do not change facial features, age, or expression.',
            '- Output a photorealistic image in the same aspect ratio as the input.',
            '',
            'ORTHODONTIC PLAN:',
        ];

        if (empty($plan)) {
            $lines[] = '- (No specific prescription on file — assume a generic alignment refinement.)';
        } else {
            foreach ($plan as $line) {
                $lines[] = '- '.$line;
            }
        }

        $lines[] = '';
        $lines[] = 'EXPECTED RESULT:';
        $lines[] = '- Even, well-aligned teeth consistent with the plan above.';
        $lines[] = '- Realistic enamel texture — avoid overly white / overly perfect "CG teeth".';
        $lines[] = '- Natural spacing; preserve canine relationships.';
        if ($mandibularOnly) {
            $lines[] = '- Arches set to mandibular only: keep the maxillary (upper) teeth IDENTICAL to the input.';
        }
        $lines[] = '';
        $lines[] = 'Return the edited image.';

        return implode("\n", $lines);
    }

    /**
     * @return string[]  human-readable lines for each populated prescription field
     */
    private function planLines(?Prescription $p): array
    {
        if ($p === null) {
            return [];
        }

        $out = [];

        if (! empty($p->arches)) {
            $out[] = 'Arches to treat: '.$this->humaniseArches($p->arches);
        }

        if ($p->ipr_enabled && ! empty($p->ipr_value)) {
            $out[] = 'IPR protocol: '.$this->humaniseIpr($p->ipr_value);
        }

        if ($p->extractions_enabled && ! empty($p->extractions_value)) {
            $out[] = 'Extractions planned: '.($p->extractions_value === 'YES' ? 'yes' : 'no');
        }

        if ($p->attachments_enabled && ! empty($p->attachments_value)) {
            $attach = $this->humaniseAttachments($p->attachments_value, $p->attachments_specific_step);
            if ($attach !== null) {
                $out[] = 'Attachments: '.$attach;
            }
        }

        if ($p->elastics_enabled && ! empty($p->elastics_value)) {
            $out[] = 'Elastics: '.($p->elastics_value === 'YES' ? 'yes' : 'no');
        }

        $movement = $this->teethFromRelation($p, 'movementRestrictions');
        if (! empty($movement)) {
            $out[] = 'Do NOT move these teeth: '.implode(', ', $movement);
        }

        $attachRestrict = $this->teethFromRelation($p, 'attachmentRestrictions');
        if (! empty($attachRestrict)) {
            $out[] = 'No attachments on these teeth: '.implode(', ', $attachRestrict);
        }

        return $out;
    }

    private function humaniseArches(string $v): string
    {
        return match (strtoupper($v)) {
            'BOTH'       => 'both arches (maxillary + mandibular)',
            'MAXILLARY'  => 'maxillary (upper) only',
            'MANDIBULAR' => 'mandibular (lower) only',
            default      => strtolower($v),
        };
    }

    private function humaniseIpr(string $v): string
    {
        return match (strtoupper($v)) {
            'NO_IPR' => 'no IPR',
            'DEFER'  => 'defer IPR decision',
            'OTHER'  => 'other IPR approach',
            default  => strtolower(str_replace('_', ' ', $v)),
        };
    }

    private function humaniseAttachments(string $v, ?int $step): ?string
    {
        $upper = strtoupper($v);
        if ($upper === 'STEP_1') {
            return 'starting at step 1';
        }
        if ($upper === 'SPECIFIC_STEP') {
            return $step ? "starting at step {$step}" : 'starting at a specified step';
        }

        return null;
    }

    private function teethFromRelation(Prescription $p, string $relation): array
    {
        // Attribute access (not method call) so tests can pre-populate via
        // setRelation() without hitting the DB, while production lazy-loads.
        $collection = $p->{$relation} ?? collect();
        $teeth = $collection->pluck('tooth_code')->filter()->values()->all();

        return array_map('strval', $teeth);
    }
}
