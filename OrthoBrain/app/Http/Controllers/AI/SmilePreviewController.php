<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\ImageEditService;
use App\Services\AI\PromptBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class SmilePreviewController extends Controller
{
    private const ALLOWED_MIMES = [
        'image/png', 'image/gif', 'image/jpeg',
        'image/tiff', 'image/bmp', 'image/heic',
        'image/webp',
    ];

    private const MAX_BYTES = 20 * 1024 * 1024;

    public function __construct(
        private readonly ImageEditService $imageEdit,
        private readonly PromptBuilder $prompts,
    ) {
    }

    // POST /dev/cases/{case}/smile-preview/generate
    // multipart: photo (frontal-smile blob)
    public function generate(Request $request, int $id): JsonResponse
    {
        $case = $this->resolveCase($id);

        $request->validate([
            'photo' => ['required', 'file', 'max:'.(self::MAX_BYTES / 1024)],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('photo');
        $mime = $file->getMimeType();
        if ($mime && ! in_array($mime, self::ALLOWED_MIMES, true)) {
            abort(422, "Unsupported image MIME type: {$mime}");
        }

        $photo = new PhotoBlob(
            tileId:   'frontal-smile',
            bytes:    (string) file_get_contents($file->getRealPath()),
            mimeType: $mime ?? 'image/jpeg',
        );

        $prescription = Prescription::where('case_id', $case->id)->first();
        $prompt = $this->prompts->forSmilePreview($prescription);

        try {
            $edited = $this->imageEdit->editImage($photo, $prompt, ['case_id' => $case->id]);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'ai_unavailable',
                'message' => 'Smile visualisation is temporarily unavailable.',
            ], 503);
        }

        return response()->json([
            'image'       => $edited->toDataUrl(),
            'provider'    => $edited->providerName,
            'generatedAt' => now()->toIso8601String(),
        ]);
    }

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
}
