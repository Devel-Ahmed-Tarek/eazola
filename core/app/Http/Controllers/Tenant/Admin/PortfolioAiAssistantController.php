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
use Modules\Portfolio\Entities\Portfolio;
use Modules\Portfolio\Entities\PortfolioCategory;

class PortfolioAiAssistantController extends Controller
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
            'portfolio_id' => 'nullable|integer|exists:portfolios,id',
            'topic' => 'nullable|string|max:8000',
            'instruction' => 'nullable|string|max:8000',
            'current_title' => 'nullable|string|max:500',
            'current_description' => 'nullable|string|max:500000',
            'current_client' => 'nullable|string|max:500',
            'current_design' => 'nullable|string|max:500',
            'current_typography' => 'nullable|string|max:500',
        ]);

        if ($validated['mode'] === 'generate' && ! $admin->can('portfolio-create')) {
            return response()->json(['success' => false, 'message' => __('You do not have permission to create portfolios.')], 403);
        }
        if ($validated['mode'] === 'refine' && ! ($admin->can('portfolio-edit') || $admin->can('portfolio-create'))) {
            return response()->json(['success' => false, 'message' => __('You do not have permission to edit portfolios.')], 403);
        }

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
        $fallbackImageId = (int) (MediaUploader::query()->whereNotNull('path')->orderByDesc('id')->value('id') ?? 0);

        $system = implode("\n", [
            'You are a professional portfolio / case-study copywriter.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON keys must be exactly: "title","description","client","design","typography","category_id","image_id","url","tags",',
            '"meta_title","meta_description","meta_fb_title","meta_fb_description","meta_tw_title","meta_tw_description".',
            'description must be clean HTML using safe tags only: p,h2,h3,ul,ol,li,strong,em,br,a.',
            'tags is a comma-separated string (no #). url may be empty or a relevant https link.',
            'Pick category_id from this list only: '.json_encode($categoryMap, JSON_UNESCAPED_UNICODE),
            'If image is unknown use image_id='.$fallbackImageId.'.',
            'Write in language/locale hint: '.$lang,
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or brief for the portfolio item.')], 422);
                }
                $userMessage = "Create a complete portfolio / case study draft.\n\nBrief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to change the portfolio content.')], 422);
                }
                $pid = (int) ($validated['portfolio_id'] ?? 0);
                $title = (string) ($validated['current_title'] ?? '');
                $desc = (string) ($validated['current_description'] ?? '');
                $client = (string) ($validated['current_client'] ?? '');
                $design = (string) ($validated['current_design'] ?? '');
                $typography = (string) ($validated['current_typography'] ?? '');
                if ($pid > 0 && ($title === '' && $desc === '')) {
                    $p = Portfolio::find($pid);
                    if ($p) {
                        $title = (string) $p->getTranslation('title', $lang);
                        $desc = (string) $p->getTranslation('description', $lang);
                        $client = (string) $p->getTranslation('client', $lang);
                        $design = (string) $p->getTranslation('design', $lang);
                        $typography = (string) $p->getTranslation('typography', $lang);
                    }
                }
                $userMessage = "Current portfolio (locale ".$lang."):\nTitle: ".$title
                    ."\nClient: ".$client."\nDesign: ".$design."\nTypography: ".$typography
                    ."\n\nHTML description:\n".$desc."\n\nRequested edits:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($userMessage, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.55,
                'max_tokens' => min(16384, max((int) config('openai.blog_assist_max_tokens', 4096), 6000)),
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
            Log::warning('Portfolio AI assist failed', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Portfolio AI assist exception', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('AI could not produce valid portfolio content. Try again.')], 502);
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
        $slugList = implode(', ', $langSlugs);

        $system = implode("\n", [
            'You are a professional multilingual portfolio / case-study copywriter.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON must contain keys: "category_id","image_id","url","tags","translations".',
            'translations must include exactly these locale slugs: '.$slugList,
            'Each locale item must include: "title","description","client","design","typography",',
            '"meta_title","meta_description","meta_fb_title","meta_fb_description","meta_tw_title","meta_tw_description".',
            'description must be clean HTML: p,h2,h3,ul,ol,li,strong,em,br,a.',
            'tags is a comma-separated string shared across locales (same value for all if one phrase).',
            'Pick category_id from this list only: '.json_encode($categoryMap, JSON_UNESCAPED_UNICODE),
            'If image unknown use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or brief for the portfolio item.')], 422);
                }
                $userMessage = "Create a complete portfolio / case study in ALL locales.\n\nBrief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to change the portfolio content.')], 422);
                }
                $pid = (int) ($validated['portfolio_id'] ?? 0);
                if ($pid <= 0) {
                    return response()->json(['success' => false, 'message' => __('To improve all languages, save the portfolio first then edit it.')], 422);
                }
                $item = Portfolio::find($pid);
                if (! $item) {
                    return response()->json(['success' => false, 'message' => __('Portfolio not found.')], 404);
                }
                $perLang = [];
                foreach ($langSlugs as $slug) {
                    $perLang[$slug] = [
                        'title' => (string) $item->getTranslation('title', $slug),
                        'description' => (string) $item->getTranslation('description', $slug),
                        'client' => (string) $item->getTranslation('client', $slug),
                        'design' => (string) $item->getTranslation('design', $slug),
                        'typography' => (string) $item->getTranslation('typography', $slug),
                    ];
                }
                $userMessage = "Current portfolio by locale (JSON):\n".json_encode($perLang, JSON_UNESCAPED_UNICODE)
                    ."\n\nShared: url=".((string) $item->url).', tags='.((string) $item->tags)
                    ."\n\nRequested edits:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($userMessage, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.55,
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
                'url' => (string) ($payload['url'] ?? ''),
                'tags' => (string) ($payload['tags'] ?? ''),
                'translations' => $payload['translations'],
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('Portfolio AI all-language assist failed', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Portfolio AI all-language assist exception', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('AI could not produce valid portfolio content. Try again.')], 502);
        }
    }

    protected function buildCategoryMap(string $lang): array
    {
        $categoryMap = PortfolioCategory::query()->where('status', 1)->get()->map(function (PortfolioCategory $cat) use ($lang) {
            return ['id' => (int) $cat->id, 'title' => (string) $cat->getTranslation('title', $lang)];
        })->values()->all();

        if ($categoryMap === []) {
            $fallback = new PortfolioCategory;
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
        $rows = PortfolioCategory::query()->where('status', 1)->get();
        if ($rows->isEmpty()) {
            $fallback = new PortfolioCategory;
            $def = $langSlugs[0] ?? app()->getLocale();
            $fallback->setTranslation('title', $def, SanitizeInput::esc_html(__('General')));
            $fallback->status = 1;
            $fallback->save();
            $rows = collect([$fallback]);
        }

        return $rows->map(function (PortfolioCategory $cat) use ($langSlugs) {
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
            'client' => (string) ($data['client'] ?? ''),
            'design' => (string) ($data['design'] ?? ''),
            'typography' => (string) ($data['typography'] ?? ''),
            'category_id' => (int) ($data['category_id'] ?? 0),
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
            'url' => (string) ($data['url'] ?? ''),
            'tags' => (string) ($data['tags'] ?? ''),
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
                'client' => (string) ($t['client'] ?? ''),
                'design' => (string) ($t['design'] ?? ''),
                'typography' => (string) ($t['typography'] ?? ''),
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
            'url' => (string) ($data['url'] ?? ''),
            'tags' => (string) ($data['tags'] ?? ''),
            'translations' => $out,
        ];
    }
}
