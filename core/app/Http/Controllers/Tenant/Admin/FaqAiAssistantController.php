<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaqAiAssistantController extends Controller
{
    public function assist(Request $request, OpenAIChatService $openai): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'required|string|in:generate,refine',
            'lang' => 'nullable|string|max:20',
            'all_languages' => 'nullable|boolean',
            'faq_id' => 'nullable|integer|exists:faqs,id',
            'topic' => 'nullable|string|max:4000',
            'instruction' => 'nullable|string|max:4000',
            'current_title' => 'nullable|string|max:500',
            'current_description' => 'nullable|string|max:20000',
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
        $categoryMap = $this->buildCategoryMap($lang);

        $system = implode("\n", [
            'You are an FAQ content assistant for websites.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON keys must be exactly: "title","description","category_id".',
            'title should be a concise question.',
            'description should be a clear, direct answer in plain text.',
            'Pick category_id from this list only: '.json_encode($categoryMap, JSON_UNESCAPED_UNICODE),
            'Write in language/locale hint: '.$lang,
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic for the FAQ.')], 422);
                }
                $user = "Generate one FAQ item.\n\nTopic/brief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to improve the FAQ.')], 422);
                }
                $user = "Current FAQ title:\n".($validated['current_title'] ?? '')."\n\nCurrent answer:\n".($validated['current_description'] ?? '')."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.6,
                'max_tokens' => 1200,
            ]);

            $payload = $this->decodeSingleJson($result->content);
            $validIds = collect($categoryMap)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $payload['category_id'], $validIds, true)) {
                $payload['category_id'] = (int) ($validIds[0] ?? 0);
            }

            return response()->json(['success' => true, 'all_languages' => false] + $payload);
        } catch (OpenAIServiceException $e) {
            Log::warning('FAQ AI assist failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('FAQ AI assist exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('AI could not produce valid FAQ content. Try again.')], 502);
        }
    }

    protected function assistAllLanguages(array $validated, OpenAIChatService $openai): JsonResponse
    {
        $langSlugs = GlobalLanguage::all_languages(1)->pluck('slug')->filter()->values()->all();
        if ($langSlugs === []) {
            $langSlugs = [app()->getLocale()];
        }

        $categoryMap = $this->buildCategoryMapMulti($langSlugs);

        $system = implode("\n", [
            'You are a multilingual FAQ assistant.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON must contain keys: "category_id","translations".',
            'translations must include all locale slugs: '.implode(', ', $langSlugs),
            'Each locale translation must contain: "title","description".',
            'title must be a concise question and description a clear direct answer.',
            'Pick category_id from this list only: '.json_encode($categoryMap, JSON_UNESCAPED_UNICODE),
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic for the FAQ.')], 422);
                }
                $user = "Generate one FAQ item in ALL locales.\n\nTopic/brief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to improve the FAQ.')], 422);
                }

                $faqId = (int) ($validated['faq_id'] ?? 0);
                if ($faqId <= 0) {
                    return response()->json(['success' => false, 'message' => __('To improve all languages, save the FAQ first then edit it.')], 422);
                }
                $faq = Faq::find($faqId);
                if (!$faq) {
                    return response()->json(['success' => false, 'message' => __('FAQ item not found.')], 404);
                }

                $perLang = [];
                foreach ($langSlugs as $slug) {
                    $perLang[$slug] = [
                        'title' => (string) $faq->getTranslation('title', $slug),
                        'description' => (string) $faq->getTranslation('description', $slug),
                    ];
                }
                $user = "Current FAQ by locale (JSON):\n".json_encode($perLang, JSON_UNESCAPED_UNICODE)."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.6,
                'max_tokens' => 3000,
            ]);

            $payload = $this->decodeAllLangJson($result->content, $langSlugs);
            $validIds = collect($categoryMap)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $payload['category_id'], $validIds, true)) {
                $payload['category_id'] = (int) ($validIds[0] ?? 0);
            }

            return response()->json([
                'success' => true,
                'all_languages' => true,
                'category_id' => $payload['category_id'],
                'translations' => $payload['translations'],
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('FAQ AI all-language assist failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('FAQ AI all-language assist exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('AI could not produce valid FAQ content. Try again.')], 502);
        }
    }

    protected function buildCategoryMap(string $lang): array
    {
        $categoryMap = FaqCategory::query()->get()->map(function (FaqCategory $cat) use ($lang) {
            return ['id' => (int) $cat->id, 'title' => (string) $cat->getTranslation('title', $lang)];
        })->values()->all();

        if ($categoryMap === []) {
            $fallback = new FaqCategory();
            $fallback->setTranslation('title', $lang, SanitizeInput::esc_html(__('General')));
            $fallback->status = 1;
            $fallback->save();
            $categoryMap = [[
                'id' => (int) $fallback->id,
                'title' => (string) $fallback->getTranslation('title', $lang),
            ]];
        }

        return $categoryMap;
    }

    protected function buildCategoryMapMulti(array $langSlugs): array
    {
        $rows = FaqCategory::query()->get();
        if ($rows->isEmpty()) {
            $fallback = new FaqCategory();
            $def = $langSlugs[0] ?? app()->getLocale();
            $fallback->setTranslation('title', $def, SanitizeInput::esc_html(__('General')));
            $fallback->status = 1;
            $fallback->save();
            $rows = collect([$fallback]);
        }

        return $rows->map(function (FaqCategory $cat) use ($langSlugs) {
            $titles = [];
            foreach ($langSlugs as $slug) {
                $titles[$slug] = (string) $cat->getTranslation('title', $slug);
            }
            return ['id' => (int) $cat->id, 'titles' => $titles];
        })->values()->all();
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
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'category_id' => (int) ($data['category_id'] ?? 0),
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
                'title' => (string) ($t['title'] ?? ''),
                'description' => (string) ($t['description'] ?? ''),
            ];
        }

        return [
            'category_id' => (int) ($data['category_id'] ?? 0),
            'translations' => $out,
        ];
    }
}
