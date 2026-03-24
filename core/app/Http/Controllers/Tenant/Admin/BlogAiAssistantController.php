<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use App\Models\MediaUploader;
use App\Helpers\SanitizeInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Blog\Entities\BlogCategory;

class BlogAiAssistantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * توليد مسودة مقال أو تعديل محتوى HTML الحالي عبر OpenAI + مرجع الموقع.
     */
    public function assist(Request $request, OpenAIChatService $openai): JsonResponse
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403);
        }

        $validated = $request->validate([
            'mode'            => 'required|string|in:generate,refine',
            'lang'            => 'nullable|string|max:20',
            'topic'           => 'nullable|string|max:8000',
            'instruction'     => 'nullable|string|max:8000',
            'current_content' => 'nullable|string|max:500000',
        ]);

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

        $lang = $validated['lang'] ?? app()->getLocale();
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

        if (empty($categoryMap)) {
            $fallbackCategory = new BlogCategory();
            $fallbackCategory->setTranslation('title', $lang, SanitizeInput::esc_html(__('General')));
            $fallbackCategory->status = 1;
            $fallbackCategory->save();

            $categoryMap = [[
                'id' => (int) $fallbackCategory->id,
                'title' => (string) $fallbackCategory->getTranslation('title', $lang),
            ]];
        }

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
                    $this->blogAiOptions()
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
                    $this->blogAiOptions()
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
                'success'       => true,
                'title'         => $payload['title'],
                'excerpt'       => $payload['excerpt'],
                'blog_content'  => $payload['blog_content'],
                'category_id'   => $payload['category_id'],
                'image_id'      => $payload['image_id'],
                'meta_title'    => $payload['meta_title'],
                'meta_description' => $payload['meta_description'],
                'meta_fb_title' => $payload['meta_fb_title'],
                'meta_fb_description' => $payload['meta_fb_description'],
                'meta_tw_title' => $payload['meta_tw_title'],
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
     * @return array<string, mixed>
     */
    protected function blogAiOptions(): array
    {
        return [
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.65,
            'max_tokens'      => (int) config('openai.blog_assist_max_tokens', 4096),
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
            'title'        => (string) ($data['title'] ?? ''),
            'excerpt'      => mb_substr((string) ($data['excerpt'] ?? ''), 0, 191),
            'blog_content' => (string) ($data['blog_content'] ?? ''),
            'category_id'  => (int) ($data['category_id'] ?? 0),
            'image_id'     => max(0, (int) ($data['image_id'] ?? 0)),
            'meta_title'   => (string) ($data['meta_title'] ?? ''),
            'meta_description' => (string) ($data['meta_description'] ?? ''),
            'meta_fb_title' => (string) ($data['meta_fb_title'] ?? ''),
            'meta_fb_description' => (string) ($data['meta_fb_description'] ?? ''),
            'meta_tw_title' => (string) ($data['meta_tw_title'] ?? ''),
            'meta_tw_description' => (string) ($data['meta_tw_description'] ?? ''),
        ];
    }
}
