<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Facades\GlobalLanguage;
use App\Http\Controllers\Controller;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use App\Models\MediaUploader;
use App\Helpers\SanitizeInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Blog\Actions\Blog\BlogAction;
use Modules\Blog\Entities\Blog;
use Modules\Blog\Entities\BlogCategory;

class BlogAiAssistantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * توليد مسودة مقال أو تعديل محتوى HTML الحالي عبر OpenAI + مرجع الموقع.
     * يدعم لغة واحدة أو كل لغات الموقع دفعة واحدة (all_languages).
     */
    public function assist(Request $request, OpenAIChatService $openai): JsonResponse
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403);
        }

        $validated = $request->validate([
            'mode'              => 'required|string|in:generate,refine',
            'lang'              => 'nullable|string|max:20',
            'all_languages'     => 'nullable|boolean',
            'blog_id'           => 'nullable|integer|exists:blogs,id',
            'topic'             => 'nullable|string|max:8000',
            'instruction'       => 'nullable|string|max:8000',
            'current_content'   => 'nullable|string|max:500000',
        ]);

        $allLanguages = filter_var($validated['all_languages'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($validated['mode'] === 'generate' && ! $admin->can('blog-create')) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to create blog posts.'),
            ], 403);
        }

        if ($validated['mode'] === 'refine' && ! ($admin->can('blog-edit') || $admin->can('blog-create'))) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to edit blog content.'),
            ], 403);
        }

        if (! $openai->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('OpenAI API is not configured. Add OPENAI_API_KEY in .env and clear config cache.'),
            ], 422);
        }

        if ($allLanguages) {
            return $this->assistAllLanguages($validated, $openai);
        }

        return $this->assistSingleLanguage($validated, $openai);
    }

    /**
     * حفظ ترجمات كل اللغات على مقال موجود (بعد توليد الذكاء الاصطناعي).
     */
    public function applyTranslations(Request $request, BlogAction $blogAction): JsonResponse
    {
        $admin = auth('admin')->user();
        if (! $admin || ! $admin->can('blog-edit')) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to edit blog posts.'),
            ], 403);
        }

        $validated = $request->validate([
            'blog_id'       => 'required|integer|exists:blogs,id',
            'translations'  => 'required|array',
            'category_id'   => 'nullable|integer',
            'image_id'      => 'nullable|integer',
        ]);

        try {
            $blog = Blog::findOrFail($validated['blog_id']);

            if (! empty($validated['category_id'])) {
                $blog->category_id = (int) $validated['category_id'];
            }
            if (! empty($validated['image_id'])) {
                $blog->image = (string) $validated['image_id'];
            }
            $blog->save();

            /** @var array<string, array<string, mixed>> $translations */
            $translations = $validated['translations'];
            $blogAction->applyAiBulkTranslationsArray($blog, $translations);

            return response()->json([
                'success' => true,
                'message' => __('All language versions have been saved. You can switch the language selector to review.'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Blog AI apply translations failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('Could not save translations. Try again.'),
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function assistSingleLanguage(array $validated, OpenAIChatService $openai): JsonResponse
    {
        $lang = $validated['lang'] ?? app()->getLocale();
        $categoryMap = $this->buildCategoryMap($lang);

        $fallbackImageId = MediaUploader::query()
            ->whereNotNull('path')
            ->orderByDesc('id')
            ->value('id');

        $jsonSystemExtra = implode("\n", [
            'You are a professional blog editor for a website.',
            'You must respond with ONE valid JSON object only (no markdown fences, no commentary).',
            'The JSON must have exactly these keys: "title" (string), "excerpt" (string, max 190 characters), "blog_content" (string, HTML fragment), "category_id" (integer), "image_id" (integer|null), "meta_title" (string), "meta_description" (string), "meta_fb_title" (string), "meta_fb_description" (string), "meta_tw_title" (string), "meta_tw_description" (string).',
            'Use only safe HTML tags in blog_content: p, h2, h3, ul, ol, li, strong, em, br, a (href only). No script or style tags.',
            'Match the language of the user request (language/locale hint: '.$lang.').',
            'Pick category_id from this exact list only: '.json_encode($categoryMap, JSON_UNESCAPED_UNICODE),
            'If no image can be inferred, set image_id to '.(int) ($fallbackImageId ?: 0).'.',
            'SEO fields must be concise and relevant to the generated content.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json([
                        'success' => false,
                        'message' => __('Please enter a topic or short brief for the article.'),
                    ], 422);
                }

                $userMessage = "Write a blog post draft.\n\nTopic / brief:\n".$topic
                    ."\n\nRespond with ONE JSON object only (keys: title, excerpt, blog_content) as specified in your instructions.";
                $result = $openai->chatWithSiteReference(
                    $userMessage,
                    $jsonSystemExtra,
                    null,
                    $this->blogAiOptions(false)
                );
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                $current = (string) ($validated['current_content'] ?? '');
                if ($instruction === '') {
                    return response()->json([
                        'success' => false,
                        'message' => __('Please describe how you want to change the content.'),
                    ], 422);
                }
                if (trim(strip_tags($current)) === '') {
                    return response()->json([
                        'success' => false,
                        'message' => __('Editor is empty. Add some content first, or use Generate draft.'),
                    ], 422);
                }

                $userMessage = "Here is the current blog HTML:\n---\n".$current."\n---\n\n".
                    "Instruction for revision:\n".$instruction."\n\n".
                    'Return ONE JSON object only with keys title, excerpt, blog_content (all fields must be present).';

                $result = $openai->chatWithSiteReference(
                    $userMessage,
                    $jsonSystemExtra,
                    null,
                    $this->blogAiOptions(false)
                );
            }

            $payload = $this->decodeBlogAiJson($result->content);
            $validCategoryIds = collect($categoryMap)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $payload['category_id'], $validCategoryIds, true)) {
                $payload['category_id'] = (int) ($validCategoryIds[0] ?? 0);
            }
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = (int) ($fallbackImageId ?: 0);
            }

            return response()->json([
                'success'             => true,
                'all_languages'       => false,
                'title'               => $payload['title'],
                'excerpt'             => $payload['excerpt'],
                'blog_content'        => $payload['blog_content'],
                'category_id'         => $payload['category_id'],
                'image_id'            => $payload['image_id'],
                'meta_title'          => $payload['meta_title'],
                'meta_description'    => $payload['meta_description'],
                'meta_fb_title'       => $payload['meta_fb_title'],
                'meta_fb_description' => $payload['meta_fb_description'],
                'meta_tw_title'       => $payload['meta_tw_title'],
                'meta_tw_description' => $payload['meta_tw_description'],
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('Blog AI assist failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            Log::error('Blog AI assist exception', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('AI could not produce valid content. Try again or shorten the text.'),
            ], 502);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function assistAllLanguages(array $validated, OpenAIChatService $openai): JsonResponse
    {
        $langs = GlobalLanguage::all_languages(1);
        $langSlugs = $langs->pluck('slug')->filter()->values()->all();

        if ($langSlugs === []) {
            $langSlugs = [app()->getLocale()];
        }

        $categoryMap = $this->buildCategoryMapMulti($langSlugs);
        $fallbackImageId = MediaUploader::query()
            ->whereNotNull('path')
            ->orderByDesc('id')
            ->value('id');

        $slugList = implode(', ', $langSlugs);
        $jsonSystemExtra = implode("\n", [
            'You are a professional multilingual blog editor.',
            'You must respond with ONE valid JSON object only (no markdown fences, no commentary).',
            'The JSON must have keys: "category_id" (integer), "image_id" (integer), "translations" (object).',
            'Inside "translations", you MUST have one key for EACH of these locale slugs exactly: '.$slugList,
            'Each locale value must be an object with keys: "title", "excerpt" (max 190 chars), "blog_content" (HTML), "meta_title", "meta_description", "meta_fb_title", "meta_fb_description", "meta_tw_title", "meta_tw_description".',
            'Write each locale in the correct natural language for that locale (do not leave any locale empty).',
            'Use only safe HTML in blog_content: p, h2, h3, ul, ol, li, strong, em, br, a (href only).',
            'Pick category_id from this list only: '.json_encode($categoryMap, JSON_UNESCAPED_UNICODE),
            'If unsure about image, set image_id to '.(int) ($fallbackImageId ?: 0).'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json([
                        'success' => false,
                        'message' => __('Please enter a topic or short brief for the article.'),
                    ], 422);
                }

                $userMessage = "Write a complete blog post for ALL listed languages.\n\nTopic / brief:\n".$topic
                    ."\n\nReturn ONE JSON object with category_id, image_id, and translations for every locale slug.";
                $result = $openai->chatWithSiteReference(
                    $userMessage,
                    $jsonSystemExtra,
                    null,
                    $this->blogAiOptions(true)
                );
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json([
                        'success' => false,
                        'message' => __('Please describe how you want to change the content.'),
                    ], 422);
                }

                $blogId = (int) ($validated['blog_id'] ?? 0);
                if ($blogId <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => __('To improve all languages, save the post first, then use this option on the edit screen.'),
                    ], 422);
                }

                $blog = Blog::find($blogId);
                if ($blog === null) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Post not found.'),
                    ], 404);
                }

                $perLang = [];
                foreach ($langSlugs as $slug) {
                    $perLang[$slug] = [
                        'title'         => (string) $blog->getTranslation('title', $slug),
                        'excerpt'       => (string) $blog->getTranslation('excerpt', $slug),
                        'blog_content'  => (string) $blog->getTranslation('blog_content', $slug),
                    ];
                }

                $userMessage = "Current article content per language (JSON):\n".json_encode($perLang, JSON_UNESCAPED_UNICODE)
                    ."\n\nRevision instruction:\n".$instruction
                    ."\n\nReturn ONE JSON object with category_id, image_id, and full translations for every locale slug.";

                $result = $openai->chatWithSiteReference(
                    $userMessage,
                    $jsonSystemExtra,
                    null,
                    $this->blogAiOptions(true)
                );
            }

            $payload = $this->decodeBlogAiAllLangJson($result->content, $langSlugs);
            $validCategoryIds = collect($categoryMap)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $payload['category_id'], $validCategoryIds, true)) {
                $payload['category_id'] = (int) ($validCategoryIds[0] ?? 0);
            }
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = (int) ($fallbackImageId ?: 0);
            }

            return response()->json([
                'success'       => true,
                'all_languages' => true,
                'category_id'   => $payload['category_id'],
                'image_id'      => $payload['image_id'],
                'translations'  => $payload['translations'],
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('Blog AI assist (all languages) failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            Log::error('Blog AI assist (all languages) exception', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('AI could not produce valid content. Try again, reduce languages, or shorten the topic.'),
            ], 502);
        }
    }

    /**
     * @return array<int, array{id: int, title: string}>
     */
    protected function buildCategoryMap(string $lang): array
    {
        $categoryMap = BlogCategory::query()
            ->where('status', 1)
            ->get()
            ->map(function (BlogCategory $cat) use ($lang) {
                return [
                    'id' => (int) $cat->id,
                    'title' => (string) $cat->getTranslation('title', $lang),
                ];
            })
            ->values()
            ->all();

        if ($categoryMap === []) {
            $fallbackCategory = new BlogCategory();
            $fallbackCategory->setTranslation('title', $lang, SanitizeInput::esc_html(__('General')));
            $fallbackCategory->status = 1;
            $fallbackCategory->save();

            $categoryMap = [[
                'id' => (int) $fallbackCategory->id,
                'title' => (string) $fallbackCategory->getTranslation('title', $lang),
            ]];
        }

        return $categoryMap;
    }

    /**
     * @param  array<int, string>  $langSlugs
     * @return array<int, array{id: int, titles: array<string, string>}>
     */
    protected function buildCategoryMapMulti(array $langSlugs): array
    {
        $rows = BlogCategory::query()
            ->where('status', 1)
            ->get();

        if ($rows->isEmpty()) {
            $fallbackCategory = new BlogCategory();
            $def = $langSlugs[0] ?? app()->getLocale();
            $fallbackCategory->setTranslation('title', $def, SanitizeInput::esc_html(__('General')));
            $fallbackCategory->status = 1;
            $fallbackCategory->save();
            $rows = collect([$fallbackCategory]);
        }

        return $rows->map(function (BlogCategory $cat) use ($langSlugs) {
            $titles = [];
            foreach ($langSlugs as $slug) {
                $titles[$slug] = (string) $cat->getTranslation('title', $slug);
            }

            return [
                'id' => (int) $cat->id,
                'titles' => $titles,
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function blogAiOptions(bool $allLanguages): array
    {
        $max = (int) config('openai.blog_assist_max_tokens', 4096);

        return [
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.65,
            'max_tokens'      => $allLanguages ? min(16384, max($max, 8192)) : $max,
        ];
    }

    /**
     * @return array{title: string, excerpt: string, blog_content: string, category_id: int, image_id: int, meta_title: string, meta_description: string, meta_fb_title: string, meta_fb_description: string, meta_tw_title: string, meta_tw_description: string}
     */
    protected function decodeBlogAiJson(string $raw): array
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
            'title'               => (string) ($data['title'] ?? ''),
            'excerpt'             => mb_substr((string) ($data['excerpt'] ?? ''), 0, 191),
            'blog_content'        => (string) ($data['blog_content'] ?? ''),
            'category_id'         => (int) ($data['category_id'] ?? 0),
            'image_id'            => max(0, (int) ($data['image_id'] ?? 0)),
            'meta_title'          => (string) ($data['meta_title'] ?? ''),
            'meta_description'    => (string) ($data['meta_description'] ?? ''),
            'meta_fb_title'       => (string) ($data['meta_fb_title'] ?? ''),
            'meta_fb_description' => (string) ($data['meta_fb_description'] ?? ''),
            'meta_tw_title'       => (string) ($data['meta_tw_title'] ?? ''),
            'meta_tw_description' => (string) ($data['meta_tw_description'] ?? ''),
        ];
    }

    /**
     * @param  array<int, string>  $expectedSlugs
     * @return array{category_id: int, image_id: int, translations: array<string, array<string, string>>}
     */
    protected function decodeBlogAiAllLangJson(string $raw, array $expectedSlugs): array
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/u', $raw, $m)) {
            $raw = trim($m[1]);
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            throw new \RuntimeException('invalid_json');
        }

        $translationsIn = $data['translations'] ?? [];
        if (! is_array($translationsIn)) {
            throw new \RuntimeException('invalid_json');
        }

        $out = [];
        foreach ($expectedSlugs as $slug) {
            $t = $translationsIn[$slug] ?? [];
            if (! is_array($t)) {
                $t = [];
            }
            $out[$slug] = [
                'title'               => (string) ($t['title'] ?? ''),
                'excerpt'             => mb_substr((string) ($t['excerpt'] ?? ''), 0, 191),
                'blog_content'        => (string) ($t['blog_content'] ?? ''),
                'meta_title'          => (string) ($t['meta_title'] ?? ''),
                'meta_description'    => (string) ($t['meta_description'] ?? ''),
                'meta_fb_title'       => (string) ($t['meta_fb_title'] ?? ''),
                'meta_fb_description' => (string) ($t['meta_fb_description'] ?? ''),
                'meta_tw_title'       => (string) ($t['meta_tw_title'] ?? ''),
                'meta_tw_description' => (string) ($t['meta_tw_description'] ?? ''),
            ];
        }

        return [
            'category_id' => (int) ($data['category_id'] ?? 0),
            'image_id'    => max(0, (int) ($data['image_id'] ?? 0)),
            'translations'=> $out,
        ];
    }
}
