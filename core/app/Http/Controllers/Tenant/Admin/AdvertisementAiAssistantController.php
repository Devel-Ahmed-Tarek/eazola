<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Facades\GlobalLanguage;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\MediaUploader;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdvertisementAiAssistantController extends Controller
{
    private const ALLOWED_TYPES = ['image', 'google_adsense', 'scripts'];

    private const ALLOWED_SIZES = [
        '350*250', '320*50', '160*600', '300*600', '336*280', '728*90',
        '730*180', '730*210', '300*1050', '950*160', '950*200', '250*1110',
    ];

    public function assist(Request $request, OpenAIChatService $openai): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'required|string|in:generate,refine',
            'lang' => 'nullable|string|max:20',
            'all_languages' => 'nullable|boolean',
            'topic' => 'nullable|string|max:4000',
            'instruction' => 'nullable|string|max:4000',
            'current_title' => 'nullable|string|max:500',
            'advertisement_id' => 'nullable|integer|exists:advertisements,id',
        ]);

        if (! $openai->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('OpenAI API is not configured. Add OPENAI_API_KEY in .env and clear config cache.'),
            ], 422);
        }

        $allLanguages = filter_var($validated['all_languages'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($allLanguages) {
            return $this->assistAllLanguages($validated, $openai);
        }

        return $this->assistSingleLanguage($validated, $openai);
    }

    protected function assistSingleLanguage(array $validated, OpenAIChatService $openai): JsonResponse
    {
        $lang = $validated['lang'] ?? app()->getLocale();
        $fallbackImageId = (int) (MediaUploader::query()->whereNotNull('path')->orderByDesc('id')->value('id') ?? 0);
        $sizes = implode(', ', self::ALLOWED_SIZES);
        $types = implode(', ', self::ALLOWED_TYPES);

        $system = implode("\n", [
            'You are an advertisement setup assistant for a website admin.',
            'Return ONE valid JSON object only, no markdown.',
            'Keys: "title","type","size","image_id","slot","embed_code","redirect_url".',
            'type must be exactly one of: '.$types.'.',
            'size must be exactly one of: '.$sizes.'.',
            'For type "image": set redirect_url (https preferred), embed_code empty string, slot empty string; image_id should be a positive integer when possible.',
            'For type "google_adsense": set slot to a placeholder ad slot id string, redirect_url and embed_code empty strings; image_id can be 0.',
            'For type "scripts": set embed_code to a minimal safe HTML comment or placeholder script block, redirect_url and slot empty strings; image_id 0.',
            'If no image inferred for image type, use image_id='.$fallbackImageId.'.',
            'Write title in natural language for locale hint: '.$lang,
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or brief for the advertisement.')], 422);
                }
                $user = "Create one advertisement row.\n\nBrief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to change the advertisement.')], 422);
                }
                $title = trim((string) ($validated['current_title'] ?? ''));
                $id = (int) ($validated['advertisement_id'] ?? 0);
                if ($title === '' && $id > 0) {
                    $a = Advertisement::find($id);
                    if ($a) {
                        $title = (string) $a->getTranslation('title', $lang);
                    }
                }
                $user = "Current advertisement title (locale ".$lang."):\n".$title."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.45,
                'max_tokens' => 1800,
            ]);

            $payload = $this->decodeSinglePayload($result->content);
            $payload = $this->normalizePayload($payload, $fallbackImageId);

            return response()->json(['success' => true, 'all_languages' => false] + $payload);
        } catch (OpenAIServiceException $e) {
            Log::warning('Advertisement AI assist failed', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Advertisement AI assist exception', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('AI could not produce valid advertisement data. Try again.')], 502);
        }
    }

    protected function assistAllLanguages(array $validated, OpenAIChatService $openai): JsonResponse
    {
        $langSlugs = GlobalLanguage::all_languages(1)->pluck('slug')->filter()->values()->all();
        if ($langSlugs === []) {
            $langSlugs = [app()->getLocale()];
        }

        $fallbackImageId = (int) (MediaUploader::query()->whereNotNull('path')->orderByDesc('id')->value('id') ?? 0);
        $sizes = implode(', ', self::ALLOWED_SIZES);
        $types = implode(', ', self::ALLOWED_TYPES);

        $system = implode("\n", [
            'You are a multilingual advertisement assistant.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON must contain keys: "type","size","image_id","slot","embed_code","redirect_url","translations".',
            'type must be one of: '.$types.'. size must be one of: '.$sizes.'.',
            'translations must include all locale slugs: '.implode(', ', $langSlugs),
            'Each locale value must be an object with key: "title" only.',
            'Shared fields type/size/image_id/slot/embed_code/redirect_url follow same rules as single-locale assistant.',
            'If no image for image type, use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or brief for the advertisement.')], 422);
                }
                $user = "Create one advertisement row with titles in ALL locales.\n\nBrief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to change the advertisement.')], 422);
                }
                $id = (int) ($validated['advertisement_id'] ?? 0);
                if ($id <= 0) {
                    return response()->json(['success' => false, 'message' => __('To improve all languages, save the advertisement first then edit it.')], 422);
                }
                $item = Advertisement::find($id);
                if (! $item) {
                    return response()->json(['success' => false, 'message' => __('Advertisement not found.')], 404);
                }

                $perLang = [];
                foreach ($langSlugs as $slug) {
                    $perLang[$slug] = [
                        'title' => (string) $item->getTranslation('title', $slug),
                    ];
                }

                $user = "Current advertisement titles by locale (JSON):\n".json_encode($perLang, JSON_UNESCAPED_UNICODE)
                    ."\n\nCurrent shared fields — type: ".$item->type.', size: '.$item->size.", redirect_url: ".(string) $item->redirect_url."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.45,
                'max_tokens' => 3200,
            ]);

            $payload = $this->decodeAllPayload($result->content, $langSlugs);
            $payload = $this->normalizePayload($payload, $fallbackImageId);

            return response()->json([
                'success' => true,
                'all_languages' => true,
                'type' => $payload['type'],
                'size' => $payload['size'],
                'image_id' => $payload['image_id'],
                'slot' => $payload['slot'],
                'embed_code' => $payload['embed_code'],
                'redirect_url' => $payload['redirect_url'],
                'translations' => $payload['translations'],
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('Advertisement AI all-language assist failed', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Advertisement AI all-language assist exception', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('AI could not produce valid advertisement data. Try again.')], 502);
        }
    }

    private function decodeSinglePayload(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/u', $raw, $m)) {
            $raw = trim($m[1]);
        }
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            throw new \RuntimeException('invalid_json');
        }

        return [
            'title' => (string) ($data['title'] ?? ''),
            'type' => (string) ($data['type'] ?? 'image'),
            'size' => (string) ($data['size'] ?? '728*90'),
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
            'slot' => (string) ($data['slot'] ?? ''),
            'embed_code' => (string) ($data['embed_code'] ?? ''),
            'redirect_url' => (string) ($data['redirect_url'] ?? ''),
        ];
    }

    private function decodeAllPayload(string $raw, array $expectedSlugs): array
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/u', $raw, $m)) {
            $raw = trim($m[1]);
        }
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            throw new \RuntimeException('invalid_json');
        }

        $translations = is_array($data['translations'] ?? null) ? $data['translations'] : [];
        $out = [];
        foreach ($expectedSlugs as $slug) {
            $t = is_array($translations[$slug] ?? null) ? $translations[$slug] : [];
            $out[$slug] = [
                'title' => (string) ($t['title'] ?? ''),
            ];
        }

        return [
            'type' => (string) ($data['type'] ?? 'image'),
            'size' => (string) ($data['size'] ?? '728*90'),
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
            'slot' => (string) ($data['slot'] ?? ''),
            'embed_code' => (string) ($data['embed_code'] ?? ''),
            'redirect_url' => (string) ($data['redirect_url'] ?? ''),
            'translations' => $out,
        ];
    }

    private function normalizePayload(array $payload, int $fallbackImageId): array
    {
        $type = $payload['type'] ?? 'image';
        if (! in_array($type, self::ALLOWED_TYPES, true)) {
            $type = 'image';
        }
        $size = $payload['size'] ?? '728*90';
        if (! in_array($size, self::ALLOWED_SIZES, true)) {
            $size = '728*90';
        }

        $imageId = (int) ($payload['image_id'] ?? 0);
        if ($type === 'image' && $imageId <= 0) {
            $imageId = $fallbackImageId;
        }

        $payload['type'] = $type;
        $payload['size'] = $size;
        $payload['image_id'] = $imageId;

        return $payload;
    }
}
