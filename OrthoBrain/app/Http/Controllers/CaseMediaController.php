<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardsCaseStatus;
use App\Models\CaseMedia;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Server-side persistence for the Photographs and X-Rays tile sections.
 * Replaces the browser-only IndexedDB store so drafts survive across
 * browsers and admins can see what doctors uploaded.
 *
 * Doctor-only writes; admins read via $case->media on the edit view.
 */
class CaseMediaController extends Controller
{
    use GuardsCaseStatus;

    private const SECTIONS = ['photograph', 'xray'];

    public function __construct(private ImageUploadService $images) {}

    private const PHOTOGRAPH_TILES = [
        'profile', 'frontal-rest', 'frontal-smile',
        'upper-occlusal', 'frontal-bite', 'lower-occlusal',
        'right-buccal', 'frontal-retracted', 'left-buccal',
    ];

    private const XRAY_TILES = ['lateral-ceph', 'panoramic', 'full-mouth-series'];

    private const ALLOWED_MIME = [
        'image/png', 'image/jpeg', 'image/jpg', 'image/gif',
        'image/tiff', 'image/bmp', 'image/heic', 'image/heif',
    ];

    private const MAX_BYTES = 5 * 1024 * 1024; // 5 MB — matches client cap in media-tile-helpers.js.

    public function upload(Request $request, int $caseId)
    {
        $case = $this->resolveCaseForDoctor($caseId);
        $this->abortIfNotDraft($case);

        $payload = $request->validate([
            'section'     => 'required|in:' . implode(',', self::SECTIONS),
            'tile_id'     => 'required|string|max:40',
            'file'        => 'required|file|max:' . (self::MAX_BYTES / 1024),
            'crop_params' => 'nullable|string', // JSON-encoded; we store raw
        ]);

        $this->assertTileBelongsToSection($payload['section'], $payload['tile_id']);

        $file = $request->file('file');
        $mime = $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_MIME, true)) {
            abort(422, "Unsupported image MIME: {$mime}");
        }

        // One row per (case, section, tile) — replace existing on re-upload.
        $existing = CaseMedia::where('case_id', $case->id)
            ->where('section', $payload['section'])
            ->where('tile_id', $payload['tile_id'])
            ->first();
        if ($existing) {
            Storage::disk($existing->disk)->delete($existing->path);
        }

        $ext = $file->getClientOriginalExtension() ?: $this->extFromMime($mime);
        $filename = Str::uuid()->toString() . ($ext ? ".{$ext}" : '');
        $path = "case-media/{$case->id}/{$payload['section']}/{$filename}";

        $stored = Storage::disk('public')->putFileAs(
            "case-media/{$case->id}/{$payload['section']}",
            $file,
            $filename
        );
        if (! $stored) {
            abort(500, 'Could not write file to storage.');
        }

        $cropParams = null;
        if (! empty($payload['crop_params'])) {
            $decoded = json_decode($payload['crop_params'], true);
            if (is_array($decoded)) {
                $cropParams = $decoded;
            }
        }

        $media = CaseMedia::updateOrCreate(
            [
                'case_id' => $case->id,
                'section' => $payload['section'],
                'tile_id' => $payload['tile_id'],
            ],
            [
                'disk'          => 'public',
                'path'          => $path,
                'mime_type'     => $mime,
                'size_bytes'    => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
                'crop_params'   => $cropParams,
            ]
        );

        $case->touch(); // bump updated_at so draft-list "stale" filter works

        return response()->json([
            'ok'      => true,
            'id'      => $media->id,
            'section' => $media->section,
            'tile_id' => $media->tile_id,
            'url'     => $this->images->url($media->path),
            'mime'    => $media->mime_type,
            'size'    => $media->size_bytes,
        ]);
    }

    /**
     * Swap or move a tile's image to another tile slot without re-uploading
     * the file. Used when the doctor drag-rearranges already-saved photos —
     * the old destroy+upload flow nuked records that had no client-side blob,
     * causing silent data loss.
     *
     * Body: { section, source_tile_id, target_tile_id }
     *   - If a row exists at source and at target → swap their tile_ids
     *     (uses a placeholder dance to dodge the (case_id, section, tile_id)
     *     unique index)
     *   - If only source exists → rename source → target
     *   - If source has no row → idempotent 200 (nothing to reorder)
     */
    public function reorder(Request $request, int $caseId)
    {
        $case = $this->resolveCaseForDoctor($caseId);
        $this->abortIfNotDraft($case);

        $payload = $request->validate([
            'section'        => 'required|in:' . implode(',', self::SECTIONS),
            'source_tile_id' => 'required|string|max:40|different:target_tile_id',
            'target_tile_id' => 'required|string|max:40',
        ]);

        $this->assertTileBelongsToSection($payload['section'], $payload['source_tile_id']);
        $this->assertTileBelongsToSection($payload['section'], $payload['target_tile_id']);

        DB::transaction(function () use ($case, $payload) {
            $where = function () use ($case, $payload) {
                return CaseMedia::where('case_id', $case->id)
                    ->where('section', $payload['section']);
            };

            $src = $where()->where('tile_id', $payload['source_tile_id'])->first();
            $tgt = $where()->where('tile_id', $payload['target_tile_id'])->first();

            if (! $src) {
                return; // Idempotent — nothing to reorder.
            }

            if ($tgt) {
                // Swap via placeholder to dodge the unique index.
                $where()->where('tile_id', $payload['source_tile_id'])
                    ->update(['tile_id' => '__swap__']);
                $where()->where('tile_id', $payload['target_tile_id'])
                    ->update(['tile_id' => $payload['source_tile_id']]);
                $where()->where('tile_id', '__swap__')
                    ->update(['tile_id' => $payload['target_tile_id']]);
            } else {
                // Move: rename source row to target tile_id.
                $where()->where('tile_id', $payload['source_tile_id'])
                    ->update(['tile_id' => $payload['target_tile_id']]);
            }
        });

        $case->touch();

        return response()->json(['ok' => true]);
    }

    public function destroy(int $caseId, string $section, string $tileId)
    {
        $case = $this->resolveCaseForDoctor($caseId);
        $this->abortIfNotDraft($case);

        if (! in_array($section, self::SECTIONS, true)) {
            abort(404);
        }
        $this->assertTileBelongsToSection($section, $tileId);

        $media = CaseMedia::where('case_id', $case->id)
            ->where('section', $section)
            ->where('tile_id', $tileId)
            ->first();

        if (! $media) {
            // Idempotent — already gone.
            return response()->json(['ok' => true, 'message' => 'Already removed.']);
        }

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
        $case->touch();

        return response()->json(['ok' => true]);
    }

    /**
     * Match the scoping pattern used by PrescriptionController and the AI
     * controllers: doctor must own the case AND the case must belong to
     * their currently active practice.
     */
    protected function resolveCaseForDoctor(int $caseId): CaseModel
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (! $doctor) {
            abort(403, 'Doctor profile not found.');
        }

        return CaseModel::where('doctor_id', $doctor->id)
            ->where('practice_id', currentPractice()->id)
            ->findOrFail($caseId);
    }

    private function assertTileBelongsToSection(string $section, string $tileId): void
    {
        $allowed = $section === 'photograph' ? self::PHOTOGRAPH_TILES : self::XRAY_TILES;
        if (! in_array($tileId, $allowed, true)) {
            abort(422, "Invalid tile_id '{$tileId}' for section '{$section}'.");
        }
    }

    private function extFromMime(string $mime): string
    {
        $map = [
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/gif'  => 'gif',
            'image/tiff' => 'tiff',
            'image/bmp'  => 'bmp',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
        ];
        return $map[$mime] ?? '';
    }
}
