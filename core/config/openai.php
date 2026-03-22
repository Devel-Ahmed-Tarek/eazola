<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI / ChatGPT API
    |--------------------------------------------------------------------------
    |
    | مفتاح API من https://platform.openai.com/api-keys
    | اتركه فارغًا إذا لم تكن تستخدم الميزات التي تعتمد على الذكاء الاصطناعي.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    /*
    | اختياري: إن كان الحساب مرتبطًا بمنظمة OpenAI
    */
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    | عنوان الـ API (افتراضيًا OpenAI). يمكن تغييره لاحقًا لـ Azure OpenAI أو proxy.
    */
    'base_url' => rtrim(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/'),

    /*
    | النموذج الافتراضي لطلبات الدردشة (chat completions)
    */
    'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o-mini'),

    /*
    | مهلة الاتصال بالثواني
    */
    'timeout' => (int) env('OPENAI_TIMEOUT', 60),

    /*
    | إعدادات افتراضية لطلبات chat (يمكن تجاوزها من الكود)
    */
    'defaults' => [
        'temperature' => (float) env('OPENAI_DEFAULT_TEMPERATURE', 0.7),
        'max_tokens'  => env('OPENAI_DEFAULT_MAX_TOKENS') !== null
            ? (int) env('OPENAI_DEFAULT_MAX_TOKENS')
            : null,
    ],

    /*
    |--------------------------------------------------------------------------
    | مرجع الموقع للـ AI (لكل tenant عبر static_options)
    |--------------------------------------------------------------------------
    |
    | يُحرَّر من لوحة التحكم: General Settings → AI site reference
    | المفتاح الافتراضي: ai_site_reference
    |
    */
    'site_reference_option' => env('OPENAI_SITE_REFERENCE_OPTION', 'ai_site_reference'),

    /*
    | حد أقصى للتوكنات عند توليد/تعديل مقالات المدونة من لوحة التحكم
    */
    'blog_assist_max_tokens' => (int) env('OPENAI_BLOG_ASSIST_MAX_TOKENS', 4096),

];
