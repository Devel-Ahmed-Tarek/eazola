<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Facades\GlobalLanguage;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\MediaUploader;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BrandAiAssistantController extends Controller
{
    public function assist(Request $request, OpenAIChatService $openai): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'required|string|in:generate,refine',
            'lang' => 'nullable|string|max:20',
            'all_languages' => 'nullable|boolean',
            'topic' => 'nullable|string|max:4000',
            'instruction' => 'nullable|string|max:4000',
            'current_url' => 'nullable|string|max:2000',
            'brand_id' => 'nullable|integer|exists:brands,id',
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

        $system = implode("\n", [
            'You are a brand / partner logo assistant for a website.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON keys must be exactly: "url","image_id".',
            'url must be a full absolute URL (https://...) for the brand official site or a sensible landing page for that brand.',
            'If you cannot infer a real domain, use a plausible https URL that matches the brief.',
            'Write the url text appropriate for locale hint: '.$lang,
            'If no image can be inferred, use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or brief for the brand.')], 422);
                }
                $user = "Suggest one brand row for a partner logo strip.\n\nTopic/brief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to change the URL or brand.')], 422);
                }
                $currentUrl = trim((string) ($validated['current_url'] ?? ''));
                $id = (int) ($validated['brand_id'] ?? 0);
                if ($currentUrl === '' && $id > 0) {
                    $b = Brand::find($id);
                    if ($b) {
                        $currentUrl = (string) $b->getTranslation('url', $lang);
                    }
                }
                $user = "Current brand URL (locale ".$lang."):\n".$currentUrl."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.45,
                'max_tokens' => 900,
            ]);

            $payload = $this->decodeSingleJson($result->content);
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = $fallbackImageId;
            }

            return response()->json(['success' => true, 'all_languages' => false] + $payload);
        } catch (OpenAIServiceException $e) {
            Log::warning('Brand AI assist failed', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Brand AI assist exception', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('AI could not produce valid brand data. Try again.')], 502);
        }
    }

    protected function assistAllLanguages(array $validated, OpenAIChatService $openai): JsonResponse
    {
        $langSlugs = GlobalLanguage::all_languages(1)->pluck('slug')->filter()->values()->all();
        if ($langSlugs === []) {
            $langSlugs = [app()->getLocale()];
        }

        $fallbackImageId = (int) (MediaUploader::query()->whereNotNull('path')->orderByDesc('id')->value('id') ?? 0);

        $system = implode("\n", [
            'You are a multilingual brand / partner logo assistant.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON must contain keys: "image_id","translations".',
            'translations must include all locale slugs: '.implode(', ', $langSlugs),
            'Each locale value must be an object with key: "url" (full https URL appropriate for that locale/market).',
            'If no image can be inferred, use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or brief for the brand.')], 422);
                }
                $user = "Suggest one brand row in ALL locales (different url per locale if needed, e.g. language-specific official pages).\n\nTopic/brief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to change the URL or brand.')], 422);
                }
                $id = (int) ($validated['brand_id'] ?? 0);
                if ($id <= 0) {
                    return response()->json(['success' => false, 'message' => __('To improve all languages, save the brand first then edit it.')], 422);
                }
                $item = Brand::find($id);
                if (! $item) {
                    return response()->json(['success' => false, 'message' => __('Brand not found.')], 404);
                }

                $perLang = [];
                foreach ($langSlugs as $slug) {
                    $perLang[$slug] = [
                        'url' => (string) $item->getTranslation('url', $slug),
                    ];
                }

                $user = "Current brand URLs by locale (JSON):\n".json_encode($perLang, JSON_UNESCAPED_UNICODE)
                    ."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.45,
                'max_tokens' => 2400,
            ]);

            $payload = $this->decodeAllLangJson($result->content, $langSlugs);
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = $fallbackImageId;
            }

            return response()->json([
                'success' => true,
                'all_languages' => true,
                'image_id' => $payload['image_id'],
                'translations' => $payload['translations'],
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('Brand AI all-language assist failed', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Brand AI all-language assist exception', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('AI could not produce valid brand data. Try again.')], 502);
        }
    }

    private function decodeSingleJson(string $raw): array
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
            'url' => (string) ($data['url'] ?? ''),
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
        ];
    }

    private function decodeAllLangJson(string $raw, array $expectedSlugs): array
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
                'url' => (string) ($t['url'] ?? ''),
            ];
        }

        return [
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
            'translations' => $out,
        ];
    }
}
