> _Last verified: against origin/dev @ `096aea5` on 2026-05-06. If editing, update this stamp._

# 07 — AI Services

OrthoBrain uses AI for two flows on the Add Case wizard:

1. **Photo QC + Smile Plan** — Gemini/Ollama vision analysis
2. **Smile Preview** — Gemini image-edit (before/after visualisation)

All wiring is in [app/Services/AI/](../../app/Services/AI/) and config in
[config/ai.php](../../config/ai.php). The pattern is "ordered provider
chain — first one that succeeds wins, canned fallback for the demo
case".

## Configuration

[config/ai.php](../../config/ai.php) — env-driven, two independent provider chains:

```
AI_PROVIDER_CHAIN=gemini,ollama,canned          # vision (QC + smile plan)
AI_IMAGE_EDIT_PROVIDER_CHAIN=gemini,canned      # before/after image edit
```

Per-provider env vars:
- `GEMINI_API_KEY`, `AI_GEMINI_MODEL` (default `gemini-2.0-flash`), `AI_GEMINI_BASE_URL`
- `AI_OLLAMA_URL` (default `http://localhost:11434`), `AI_OLLAMA_QC_MODEL` (`moondream`), `AI_OLLAMA_SMILE_MODEL` (`llava:7b`)
- `AI_DEMO_CASE_ID` — the **only** case id allowed to fall through to canned responses; real cases surface real errors
- `AI_VISION_TIMEOUT_MS`, `AI_OLLAMA_SMILE_TIMEOUT_SECONDS`, `AI_IMAGE_EDIT_TIMEOUT_MS`
- `AI_IMAGE_EDIT_MODEL` (default `gemini-2.5-flash-image`)

## Service binding

[AiServiceProvider](../../app/Providers/AiServiceProvider.php) binds two singletons from the chains:

```php
VisionService     :: provider chain from config('ai.provider_chain')
ImageEditService  :: provider chain from config('ai.image_edit.provider_chain')
```

`makeVisionProvider($name)` and `makeImageEditProvider($name)` are
small `match` factories. Adding a new provider is a one-arm addition
in each.

## Layout

```
app/Services/AI/
├── VisionService.php              # chain executor for vision
├── ImageEditService.php           # chain executor for image edit
├── PromptBuilder.php              # prompt construction (QC, smile plan)
├── Contracts/
│   ├── VisionProvider.php         # interface: isAvailable(), classify(), smilePlan()
│   └── ImageEditProvider.php      # interface: isAvailable(), edit()
├── DTO/
│   ├── PhotoBlob.php              # tile_id + bytes + mime
│   ├── QcResult.php               # per-tile QC verdict
│   └── EditedImage.php            # generated image bytes + metadata
└── Providers/
    ├── GeminiProvider.php         # google generativelanguage v1beta
    ├── OllamaProvider.php         # local ollama
    ├── CannedFallbackProvider.php # demo-case-only
    ├── GeminiImageEditProvider.php
    └── CannedImageEditProvider.php
```

## Endpoints

All under both `/dev/cases/{case}/...` and `/admin/cases/{case}/...`
(admin can run AI on any case):

| Route name | Method | Per-route throttle | Controller |
|---|---|---|---|
| `cases.photos.classify` | POST | 30 / 60s (doctor only) | [AI/ImageAnalysisController](../../app/Http/Controllers/AI/ImageAnalysisController.php)::classify |
| `cases.smile-plan.generate` | POST | 10 / 60s (doctor only) | `ImageAnalysisController::smilePlan` |
| `cases.smile-preview.generate` | POST | 5 / 1m | [AI/SmilePreviewController](../../app/Http/Controllers/AI/SmilePreviewController.php)::generate |

Throttles protect against retry loops chewing through Gemini's
free-tier quota.

## Photo tile contract

`config('ai.photo_tiles')` lists all 9 expected tile ids — used to
validate QC responses:

```
profile, frontal-rest, frontal-smile,
upper-occlusal, frontal-bite, lower-occlusal,
right-buccal, frontal-retracted, left-buccal
```

These match the tile ids in [media-tile.blade.php](../../resources/views/content/cases/components/media-tile.blade.php) and the storage keys in `case_media.tile_id`.

## Provider chain semantics

`VisionService` and `ImageEditService` walk the chain in order:

1. For each provider, call `isAvailable()` (cheap config/health check)
2. If available, attempt the operation
3. On failure (timeout, transport error, bad response), fall through to next provider
4. If all "real" providers fail and the case id matches `AI_DEMO_CASE_ID`, the canned provider returns demo data
5. Otherwise the failure surfaces to the controller as an exception

This means: **the canned fallback is not a generic safety net.** It
only kicks in for the designated demo case. Real cases see real errors —
deliberate, so we don't ship hidden silent-failure paths.

## Prompt engineering

[PromptBuilder](../../app/Services/AI/PromptBuilder.php) constructs
the QC and smile-plan prompts. Iterations and lessons learned are
logged in [Docs/prompt-results.md](../prompt-results.md). When changing
prompts, append a new entry there with input/output samples so we can
diff prompt evolution.

## Tests

[tests/Unit/AI/](../../tests/Unit/AI/):
- `VisionServiceTest.php` — chain ordering, fallback semantics
- `ImageEditServiceTest.php`
- `CannedFallbackProviderTest.php` — demo-case gating
- `PromptBuilderTest.php`

These are unit tests with provider stubs — they don't hit Gemini or
Ollama. Real-provider testing is manual.

## Local dev recipe

For Ollama testing:
```bash
ollama serve                 # in another terminal
ollama pull moondream        # QC model
ollama pull llava:7b         # smile-plan model
AI_PROVIDER_CHAIN=ollama,canned php artisan serve
```

For Gemini, set `GEMINI_API_KEY` in `.env` and keep `gemini` in the
chain.

## Adding a new provider

1. Implement `VisionProvider` or `ImageEditProvider` contract
2. Add a `match` arm in [AiServiceProvider](../../app/Providers/AiServiceProvider.php) `makeVisionProvider` / `makeImageEditProvider`
3. Add `config/ai.php` entry under `providers.<name>`
4. Add a unit test in `tests/Unit/AI/`
5. Document the env vars in `.env.example`

## Cost / quota notes

- Gemini free tier: 15 RPM, 1M TPM, 1500 RPD on `gemini-2.0-flash` (subject to change)
- Throttles in routes/web.php are deliberately tight — when relaxing them, check the current Gemini limits first
