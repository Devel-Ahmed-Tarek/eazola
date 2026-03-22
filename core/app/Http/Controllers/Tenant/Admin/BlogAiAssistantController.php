<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $jsonSystemExtra = implode("\n", [
            'You are a professional blog editor for a website.',
            'You must respond with ONE valid JSON object only (no markdown fences, no commentary).',
            'The JSON must have exactly these keys: "title" (string), "excerpt" (string, max 190 characters), "blog_content" (string, HTML fragment).',
            'Use only safe HTML tags in blog_content: p, h2, h3, ul, ol, li, strong, em, br, a (href only). No script or style tags.',
            'Match the language of the user request (language/locale hint: '.$lang.').',
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

            return response()->json([
                'success'       => true,
                'title'         => $payload['title'],
                'excerpt'       => $payload['excerpt'],
                'blog_content'  => $payload['blog_content'],
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
     * @return array{title: string, excerpt: string, blog_content: string}
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
        ];
    }
}
