<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\VisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class ImageAnalysisController extends Controller
{
    private const ALLOWED_MIMES = [
        'image/png', 'image/gif', 'image/jpeg',
        'image/tiff', 'image/bmp', 'image/heic',
        'image/webp',
    ];

    private const MAX_BYTES = 5 * 1024 * 1024;  // 5 MB per photographs.js

    public function __construct(private readonly VisionService $vision)
    {
    }

    // POST /dev/cases/{case}/photos/classify
    // multipart: tile_id + photo
    public function classify(Request $request, int $id): JsonResponse
    {
        $case = $this->resolveCase($id);

        $data = $request->validate([
            'tile_id' => ['required', 'string', 'in:'.implode(',', config('ai.photo_tiles'))],
            'photo'   => ['required', 'file', 'max:'.(self::MAX_BYTES / 1024)],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('photo');
        $this->assertMime($file);

        $photo = new PhotoBlob(
            tileId:   $data['tile_id'],
            bytes:    (string) file_get_contents($file->getRealPath()),
            mimeType: $file->getMimeType() ?? 'image/jpeg',
        );

        try {
            $result = $this->vision->classifyPhoto($photo, ['case_id' => $case->id]);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'ai_unavailable',
                'message' => 'AI classification is temporarily unavailable.',
            ], 503);
        }

        return response()->json($result->toArray());
    }

    // POST /dev/cases/{case}/smile-plan/generate
    // multipart: photos[tile_id] -> file  (one entry per uploaded tile)
    public function smilePlan(Request $request, int $id): JsonResponse
    {
        $case = $this->resolveCase($id);

        $request->validate([
            'photos'   => ['required', 'array', 'min:4'],
            'photos.*' => ['required', 'file', 'max:'.(self::MAX_BYTES / 1024)],
        ]);

        $photos = [];
        $tileOrder = config('ai.photo_tiles');
        $tilesWhitelist = array_flip($tileOrder);
        $uploaded = $request->file('photos') ?? [];

        foreach ($uploaded as $tileId => $file) {
            if (! is_string($tileId) || ! isset($tilesWhitelist[$tileId])) {
                return response()->json([
                    'error'   => 'invalid_tile',
                    'message' => "Unknown tile id: {$tileId}",
                ], 422);
            }
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $this->assertMime($file);
            $photos[$tileId] = new PhotoBlob(
                tileId:   $tileId,
                bytes:    (string) file_get_contents($file->getRealPath()),
                mimeType: $file->getMimeType() ?? 'image/jpeg',
            );
        }

        // Send in canonical tile order so the model sees photos in a predictable sequence.
        $ordered = [];
        foreach ($tileOrder as $tileId) {
            if (isset($photos[$tileId])) {
                $ordered[] = $photos[$tileId];
            }
        }

        if (count($ordered) < 4) {
            return response()->json([
                'error'   => 'too_few_photos',
                'message' => 'At least 4 photos are required to generate a smile plan.',
            ], 422);
        }

        try {
            $narrative = $this->vision->generateSmilePlan($ordered, ['case_id' => $case->id]);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'ai_unavailable',
                'message' => 'Smile plan generation is temporarily unavailable.',
            ], 503);
        }

        return response()->json([
            'narrative'   => $narrative,
            'generatedAt' => now()->toIso8601String(),
            'photoCount'  => count($ordered),
        ]);
    }

    // Matches PrescriptionController::update scoping: admins see any case,
    // doctors only their own.
    private function resolveCase(int $id): CaseModel
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        if ($user->role === 'ADMIN') {
            return CaseModel::findOrFail($id);
        }

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (! $doctor) {
            abort(403, 'Doctor profile not found.');
        }

        return CaseModel::where('doctor_id', $doctor->id)
            ->where('practice_id', currentPractice()->id)
            ->findOrFail($id);
    }

    private function assertMime(UploadedFile $file): void
    {
        $mime = $file->getMimeType();
        if ($mime && ! in_array($mime, self::ALLOWED_MIMES, true)) {
            abort(422, "Unsupported image MIME type: {$mime}");
        }
    }
}
