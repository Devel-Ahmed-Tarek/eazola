<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Models\MediaUploader;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Knowledgebase\Entities\Knowledgebase;
use Modules\Knowledgebase\Entities\KnowledgebaseCategory;

class KnowledgebaseAiAssistantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function assist(Request $request, OpenAIChatService $openai): JsonResponse
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403);
        }

        $validated = $request->validate([
            'mode' => 'required|string|in:generate,refine',
            'lang' => 'nullable|string|max:20',
            'all_languages' => 'nullable|boolean',
            'knowledgebase_id' => 'nullable|integer|exists:knowledgebases,id',
            'topic' => 'nullable|string|max:8000',
            'instruction' => 'nullable|string|max:8000',
            'current_content' => 'nullable|string|max:500000',
            'current_title' => 'nullable|string|max:500',
        ]);

        if ($validated['mode'] === 'generate' && ! $admin->can('knowledgebase-create')) {
            return response()->json(['success' => false, 'message' => __('You do not have permission to create knowledgebase articles.')], 403);
        }
        if ($validated['mode'] === 'refine' && ! ($admin->can('knowledgebase-edit') || $admin->can('knowledgebase-create'))) {
            return response()->json(['success' => false, 'message' => __('You do not have permission to edit knowledgebase articles.')], 403);
        }
        if (! $openai->isConfigured()) {
            return response()->json(['success' => false, 'message' => __('OpenAI API is not configured. Add OPENAI_API_KEY in .env and clear config cache.')], 422);
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
        $fallbackImageId = (int) (MediaUploader::query()->whereNotNull('path')->orderByDesc('id')->value('id') ?? 0);

        $system = implode("\n", [
            'You are a professional knowledgebase writer.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON keys must be exactly: "title","description","category_id","image_id","meta_title","meta_description","meta_fb_title","meta_fb_description","meta_tw_title","meta_tw_description".',
            'description must be clean HTML fragment using safe tags only: p,h2,h3,ul,ol,li,strong,em,br,a.',
            'Pick category_id from this list only: '.json_encode($categoryMap, JSON_UNESCAPED_UNICODE),
            'If image is unknown use image_id='.$fallbackImageId.'.',
            'Write in language/locale hint: '.$lang,
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or short brief for the knowledgebase article.')], 422);
                }
                $userMessage = "Create a complete knowledgebase article draft.\n\nBrief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                $currentContent = (string) ($validated['current_content'] ?? '');
                $currentTitle = (string) ($validated['current_title'] ?? '');
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to change the knowledgebase content.')], 422);
                }
                $userMessage = "Current title:\n".$currentTitle."\n\nCurrent HTML description:\n".$currentContent."\n\nRequested edits:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($userMessage, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.6,
                'max_tokens' => (int) config('openai.blog_assist_max_tokens', 4096),
            ]);

            $payload = $this->decodeSingleJson($result->content);
            $validIds = collect($categoryMap)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $payload['category_id'], $validIds, true)) {
                $payload['category_id'] = (int) ($validIds[0] ?? 0);
            }
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = $fallbackImageId;
            }

            return response()->json(['success' => true, 'all_languages' => false] + $payload);
        } catch (OpenAIServiceException $e) {
            Log::warning('Knowledgebase AI assist failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Knowledgebase AI assist exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('AI could not produce valid knowledgebase content. Try again.')], 502);
        }
    }

    protected function assistAllLanguages(array $validated, OpenAIChatService $openai): JsonResponse
    {
        $langSlugs = GlobalLanguage::all_languages(1)->pluck('slug')->filter()->values()->all();
        if ($langSlugs === []) {
            $langSlugs = [app()->getLocale()];
        }

        $fallbackImageId = (int) (MediaUploader::query()->whereNotNull('path')->orderByDesc('id')->value('id') ?? 0);
        $categoryMap = $this->buildCategoryMapMulti($langSlugs);

        $system = implode("\n", [
            'You are a professional multilingual knowledgebase writer.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON must contain keys: "category_id","image_id","translations".',
            'translations must include all locale slugs: '.implode(', ', $langSlugs),
            'Each locale item must include: "title","description","meta_title","meta_description","meta_fb_title","meta_fb_description","meta_tw_title","meta_tw_description".',
            'Use safe HTML in description: p,h2,h3,ul,ol,li,strong,em,br,a.',
            'Pick category_id from this list only: '.json_encode($categoryMap, JSON_UNESCAPED_UNICODE),
            'If image unknown use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or short brief for the knowledgebase article.')], 422);
                }
                $userMessage = "Create a complete knowledgebase article draft in ALL locales.\n\nBrief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to change the knowledgebase content.')], 422);
                }
                $id = (int) ($validated['knowledgebase_id'] ?? 0);
                if ($id <= 0) {
                    return response()->json(['success' => false, 'message' => __('To improve all languages, save the knowledgebase first, then use this option on edit screen.')], 422);
                }
                $kb = Knowledgebase::find($id);
                if (!$kb) {
                    return response()->json(['success' => false, 'message' => __('Knowledgebase item not found.')], 404);
                }
                $perLang = [];
                foreach ($langSlugs as $slug) {
                    $perLang[$slug] = [
                        'title' => (string) $kb->getTranslation('title', $slug),
                        'description' => (string) $kb->getTranslation('description', $slug),
                    ];
                }
                $userMessage = "Current knowledgebase content by locale (JSON):\n".json_encode($perLang, JSON_UNESCAPED_UNICODE)
                    ."\n\nRequested edits:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($userMessage, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.6,
                'max_tokens' => min(16384, max((int) config('openai.blog_assist_max_tokens', 4096), 8192)),
            ]);

            $payload = $this->decodeAllLangJson($result->content, $langSlugs);
            $validIds = collect($categoryMap)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $payload['category_id'], $validIds, true)) {
                $payload['category_id'] = (int) ($validIds[0] ?? 0);
            }
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = $fallbackImageId;
            }

            return response()->json([
                'success' => true,
                'all_languages' => true,
                'category_id' => $payload['category_id'],
                'image_id' => $payload['image_id'],
                'translations' => $payload['translations'],
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('Knowledgebase AI all-language assist failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Knowledgebase AI all-language assist exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('AI could not produce valid knowledgebase content. Try again.')], 502);
        }
    }

    protected function buildCategoryMap(string $lang): array
    {
        $categoryMap = KnowledgebaseCategory::query()->where('status', 1)->get()->map(function (KnowledgebaseCategory $cat) use ($lang) {
            return ['id' => (int) $cat->id, 'title' => (string) $cat->getTranslation('title', $lang)];
        })->values()->all();

        if ($categoryMap === []) {
            $fallback = new KnowledgebaseCategory();
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
        $rows = KnowledgebaseCategory::query()->where('status', 1)->get();
        if ($rows->isEmpty()) {
            $fallback = new KnowledgebaseCategory();
            $def = $langSlugs[0] ?? app()->getLocale();
            $fallback->setTranslation('title', $def, SanitizeInput::esc_html(__('General')));
            $fallback->status = 1;
            $fallback->save();
            $rows = collect([$fallback]);
        }

        return $rows->map(function (KnowledgebaseCategory $cat) use ($langSlugs) {
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
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
            'meta_title' => (string) ($data['meta_title'] ?? ''),
            'meta_description' => (string) ($data['meta_description'] ?? ''),
            'meta_fb_title' => (string) ($data['meta_fb_title'] ?? ''),
            'meta_fb_description' => (string) ($data['meta_fb_description'] ?? ''),
            'meta_tw_title' => (string) ($data['meta_tw_title'] ?? ''),
            'meta_tw_description' => (string) ($data['meta_tw_description'] ?? ''),
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
                'meta_title' => (string) ($t['meta_title'] ?? ''),
                'meta_description' => (string) ($t['meta_description'] ?? ''),
                'meta_fb_title' => (string) ($t['meta_fb_title'] ?? ''),
                'meta_fb_description' => (string) ($t['meta_fb_description'] ?? ''),
                'meta_tw_title' => (string) ($t['meta_tw_title'] ?? ''),
                'meta_tw_description' => (string) ($t['meta_tw_description'] ?? ''),
            ];
        }

        return [
            'category_id' => (int) ($data['category_id'] ?? 0),
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
            'translations' => $out,
        ];
    }
}
