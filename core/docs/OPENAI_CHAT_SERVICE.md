# OpenAI / ChatGPT — خدمة موحّدة (`OpenAIChatService`)

## مرجع الموقع (لكل tenant)

من لوحة التحكم: **General Settings** → **AI site reference** (`/admin-home/ai-site-reference`).

- يُخزَّن النص في `static_options` باسم `ai_site_reference` (قابل للتغيير عبر `OPENAI_SITE_REFERENCE_OPTION` في `.env`).
- في الكود استخدم `chatWithSiteReference()` أو `textWithSiteReference()` لدمج هذا المرجع تلقائياً مع طلبك، أو `AiSiteContextService::composeSystemMessage()` لبناء رسالة system يدوياً.

## الإعداد

1. أنشئ مفتاح API من [OpenAI Platform](https://platform.openai.com/api-keys).
2. في ملف `.env` (في مجلد `core` حسب مشروعك):

```env
OPENAI_API_KEY=sk-...
# اختياري:
OPENAI_ORGANIZATION=
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_DEFAULT_MODEL=gpt-4o-mini
OPENAI_TIMEOUT=60
OPENAI_DEFAULT_TEMPERATURE=0.7
# OPENAI_DEFAULT_MAX_TOKENS=2048
```

3. نفّذ إن لزم: `php artisan config:clear`

## الاستخدام من الكود

### حقن الخدمة في Controller / Action

```php
use App\Services\Ai\OpenAIChatService;
use App\Services\Ai\Exceptions\OpenAIServiceException;

public function __construct(protected OpenAIChatService $openai) {}

public function draftArticle()
{
    if (! $this->openai->isConfigured()) {
        return response()->json(['error' => 'AI not configured'], 503);
    }

    try {
        $result = $this->openai->chatWithSystem(
            userMessage: 'اقترح عنوانًا ومقدمة لمقال عن الصيانة الدورية للسيارات.',
            systemMessage: 'أنت كاتب محتوى عربي. الموقع في مجال: ورش سيارات.',
        );

        return response()->json([
            'text'    => $result->content,
            'model'   => $result->model,
            'tokens'  => $result->totalTokens,
        ]);
    } catch (OpenAIServiceException $e) {
        return response()->json(['error' => $e->getMessage()], 502);
    }
}
```

### استخدام الـ helper `app()`

```php
$result = app(\App\Services\Ai\OpenAIChatService::class)->textWithSystem(
    'اكتب 3 أسئلة شائعة قصيرة عن خدماتنا.',
    'المجال: متجر إلكتروني لمنتجات عضوية.'
);
```

### رسائل متعددة (محادثة)

```php
$result = app(\App\Services\Ai\OpenAIChatService::class)->chat([
    ['role' => 'system', 'content' => 'أنت مساعد الموقع.'],
    ['role' => 'user', 'content' => 'ما الفرق بين المنتج أ والب؟'],
], options: ['temperature' => 0.5]);
```

### مفتاح مختلف لكل سياق (مستقبلًا: tenant)

```php
$svc = \App\Services\Ai\OpenAIChatService::make([
    'api_key' => $tenantOpenAiKey,
    'default_model' => 'gpt-4o-mini',
]);
$result = $svc->chatWithSystem('...');
```

## الملفات

| ملف | الوصف |
|-----|--------|
| `config/openai.php` | الإعدادات |
| `app/Services/Ai/OpenAIChatService.php` | الخدمة الرئيسية |
| `app/Services/Ai/DTOs/OpenAIChatResult.php` | نتيجة الطلب (نص + usage + raw) |
| `app/Services/Ai/Exceptions/OpenAIServiceException.php` | أخطاء الطبقة |

## ملاحظات أمان

- لا تضع مفتاح API في الواجهة الأمامية؛ استخدمه من السيرفر فقط.
- لا تسجّل (`log`) محتوى طلبات المستخدمين الحساسة بدون ضوابط.
