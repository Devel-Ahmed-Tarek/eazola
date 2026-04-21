<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiCustomPageBlueprint;
use App\Models\AiCustomPageSubmission;
use App\Models\Page;
use App\Services\Ai\CustomPageSchemaService;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageAiAssistantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function assist(Request $request, OpenAIChatService $openai, CustomPageSchemaService $schemaService): JsonResponse
    {
        $admin = auth('admin')->user();
        if (!$admin || !($admin->can('page-create') || $admin->can('page-edit'))) {
            return response()->json(['success' => false, 'message' => __('You do not have permission to use AI page assistant.')], 403);
        }

        $validated = $request->validate([
            'mode' => 'required|string|in:structured,raw_html',
            'lang' => 'nullable|string|max:20',
            'prompt' => 'nullable|string|max:12000',
            'raw_html' => 'nullable|string|max:500000',
            'page_id' => 'nullable|integer|exists:pages,id',
        ]);

        if (!$openai->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('OpenAI API is not configured. Add OPENAI_API_KEY in .env and clear config cache.'),
            ], 422);
        }

        $mode = $validated['mode'];
        $lang = $validated['lang'] ?? app()->getLocale();
        $prompt = trim((string) ($validated['prompt'] ?? ''));
        $rawHtml = (string) ($validated['raw_html'] ?? '');

        try {
            $schema = [];
            $sanitizedHtml = '';

            if ($mode === 'raw_html') {
                if (trim($rawHtml) === '') {
                    return response()->json(['success' => false, 'message' => __('Please provide custom HTML to analyze.')], 422);
                }

                $sanitizedHtml = $schemaService->sanitizeRawHtml($rawHtml);
                $candidateFields = $schemaService->extractFieldsFromHtml($sanitizedHtml);
                if ($prompt === '') {
                    $prompt = 'Analyze this custom html and produce a complete page schema with create/list bindings.';
                }

                $userMessage = "User instruction:\n".$prompt."\n\nHTML:\n".$sanitizedHtml."\n\nExtracted field candidates:\n".json_encode($candidateFields, JSON_UNESCAPED_UNICODE);
            } else {
                if ($prompt === '') {
                    return response()->json(['success' => false, 'message' => __('Please write a brief for the custom page.')], 422);
                }
                $userMessage = "Build a complete custom page schema with data bindings.\n\nBrief:\n".$prompt;
            }

            $result = $openai->chatWithSiteReference(
                $userMessage,
                $schemaService->buildSystemPrompt($mode, $lang),
                null,
                [
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.35,
                    'max_tokens' => 4096,
                ]
            );

            $decoded = $this->decodeJson($result->content);
            $schema = $schemaService->normalizeSchema($decoded);

            if ($mode === 'structured') {
                $sanitizedHtml = $schemaService->renderStarterTemplate($schema);
            } elseif ($sanitizedHtml === '') {
                $sanitizedHtml = $schemaService->renderStarterTemplate($schema);
            }

            $sanitizedHtml = $this->ensureBindingMarkers($sanitizedHtml, $schemaService);

            $bindings = $schemaService->buildDefaultBindings($schema['entity_name']);
            $requiredRoutes = $schemaService->requiredRoutes();

            if (!empty($validated['page_id'])) {
                AiCustomPageBlueprint::updateOrCreate(
                    ['page_id' => (int) $validated['page_id']],
                    [
                        'mode' => $mode,
                        'entity_name' => $schema['entity_name'],
                        'schema_json' => $schema,
                        'data_bindings' => $bindings,
                        'required_routes' => $requiredRoutes,
                        'sanitized_html' => $sanitizedHtml,
                        'ai_prompt' => $prompt,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'mode' => $mode,
                'title' => $schema['page_title'],
                'page_content' => $sanitizedHtml,
                'meta_title' => $schema['page_title'],
                'meta_description' => $schema['page_summary'],
                'meta_fb_title' => $schema['page_title'],
                'meta_fb_description' => $schema['page_summary'],
                'meta_tw_title' => $schema['page_title'],
                'meta_tw_description' => $schema['page_summary'],
                'schema_json' => $schema,
                'data_bindings' => $bindings,
                'required_routes' => $requiredRoutes,
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('Page AI assist failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Page AI assist exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('AI could not generate a valid custom page. Please try again.')], 502);
        }
    }

    public function submissions(Request $request): JsonResponse
    {
        $admin = auth('admin')->user();
        if (!$admin || !($admin->can('page-list') || $admin->can('page-edit'))) {
            return response()->json(['success' => false, 'message' => __('You do not have permission to view submissions.')], 403);
        }

        $validated = $request->validate([
            'page_id' => 'required|integer|exists:pages,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $pageId = (int) $validated['page_id'];
        $limit = (int) ($validated['limit'] ?? 25);

        $rows = AiCustomPageSubmission::query()
            ->where('page_id', $pageId)
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'payload_json', 'created_at'])
            ->map(function (AiCustomPageSubmission $row) {
                return [
                    'id' => $row->id,
                    'payload' => $row->payload_json,
                    'created_at' => optional($row->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        return response()->json(['success' => true, 'rows' => $rows]);
    }

    private function decodeJson(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/u', $raw, $m)) {
            $raw = trim($m[1]);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('invalid_json');
        }

        return $decoded;
    }

    private function ensureBindingMarkers(string $html, CustomPageSchemaService $schemaService): string
    {
        $output = trim($html);
        if ($output === '') {
            return $schemaService->renderStarterTemplate([
                'page_title' => 'Custom Page',
                'page_summary' => '',
                'fields' => [],
            ]);
        }

        if (!preg_match('/<form\b/i', $output)) {
            return $schemaService->renderStarterTemplate([
                'page_title' => 'Custom Page',
                'page_summary' => '',
                'fields' => $schemaService->extractFieldsFromHtml($output),
            ])."\n".$output;
        }

        if (!str_contains($output, 'data-ai-custom-form')) {
            $output = preg_replace('/<form\b/i', '<form data-ai-custom-form="1"', $output, 1) ?? $output;
        }

        if (!str_contains($output, 'data-ai-custom-list')) {
            $output .= '<div class="ai-custom-page" style="margin-top:16px"><h3>Latest records</h3><table class="ai-table"><thead><tr><th>#</th><th>Data</th><th>Date</th></tr></thead><tbody data-ai-custom-list="1"></tbody></table></div>';
        }

        return $output;
    }
}
