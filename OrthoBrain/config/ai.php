<?php

return [

    // Ordered provider chain. VisionService walks this list in order; each
    // provider that reports isAvailable() gets a shot. Canned is last-resort
    // and only triggers for the designated demo case id.
    'provider_chain' => array_values(array_filter(array_map(
        fn ($p) => trim($p),
        explode(',', (string) env('AI_PROVIDER_CHAIN', 'gemini,ollama,canned'))
    ))),

    // Seconds before a provider call is abandoned and the next in the chain is tried.
    // 15 s is comfortable for Gemini; Ollama on CPU for the 9-image smile plan needs
    // the per-provider override below.
    'timeout_seconds' => (int) env('AI_VISION_TIMEOUT_MS', 15000) / 1000,

    // Only the case whose id matches this env var is allowed to fall through to
    // canned responses. Real cases surface the real error.
    'demo_case_id' => env('AI_DEMO_CASE_ID'),

    'providers' => [

        'gemini' => [
            'api_key'  => env('GEMINI_API_KEY'),
            'model'    => env('AI_GEMINI_MODEL', 'gemini-2.0-flash'),
            'base_url' => env('AI_GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        ],

        'ollama' => [
            'base_url'         => env('AI_OLLAMA_URL', 'http://localhost:11434'),
            'qc_model'         => env('AI_OLLAMA_QC_MODEL', 'moondream'),
            'smile_plan_model' => env('AI_OLLAMA_SMILE_MODEL', 'llava:7b'),
            // Smile-plan generation on CPU can run long; override the global timeout.
            'smile_plan_timeout_seconds' => (int) env('AI_OLLAMA_SMILE_TIMEOUT_SECONDS', 90),
        ],

        'canned' => [
            'path' => storage_path('app/ai/demo'),
        ],

    ],

    // All 9 photograph tile ids. Used for QC response validation.
    'photo_tiles' => [
        'profile', 'frontal-rest', 'frontal-smile',
        'upper-occlusal', 'frontal-bite', 'lower-occlusal',
        'right-buccal', 'frontal-retracted', 'left-buccal',
    ],

];
