<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Question Bank AI generation (QB AI Generate page).
 *
 * Required in .env:
 *   google.api_key = "your-google-ai-studio-api-key"
 *
 * Optional:
 *   qb.ai.model = "gemini-2.5-pro"
 *
 * Enable pay-as-you-go billing in Google AI Studio for production volume.
 * Google One storage plans (30GB/100GB) are unrelated to API usage.
 */
class QbAi extends BaseConfig
{
    /** @var string Gemini model id for generateContent */
    public string $model = 'gemini-2.5-pro';

    public function __construct()
    {
        parent::__construct();
        $envModel = getenv('qb.ai.model');
        if (is_string($envModel) && $envModel !== '') {
            $this->model = $envModel;
        }
    }
}
