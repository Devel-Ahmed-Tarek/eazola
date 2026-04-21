<?php

namespace App\Http\Controllers\Tenant\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AiCustomPageBlueprint;
use App\Models\AiCustomPageSubmission;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiCustomPageDataController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (!$this->aiTablesReady()) {
            return response()->json(['success' => false, 'message' => __('AI custom page storage is not ready yet.')], 503);
        }

        $page = $this->resolvePageFromRequest($request);
        if (!$page) {
            return response()->json(['success' => false, 'message' => __('Target page not found.')], 404);
        }

        $blueprint = AiCustomPageBlueprint::where('page_id', $page->id)->first();
        if (!$blueprint) {
            return response()->json(['success' => false, 'message' => __('No AI data blueprint found for this page.')], 422);
        }

        $schema = is_array($blueprint->schema_json) ? $blueprint->schema_json : [];
        $fieldNames = collect((array) Arr::get($schema, 'fields', []))
            ->pluck('name')
            ->filter(fn ($v) => is_string($v) && $v !== '')
            ->values()
            ->all();

        if ($fieldNames === []) {
            return response()->json(['success' => false, 'message' => __('No fields configured for this AI page form.')], 422);
        }

        $payload = [];
        foreach ($fieldNames as $field) {
            $payload[$field] = $this->sanitizeSubmittedValue($request->input($field));
        }

        $submission = AiCustomPageSubmission::create([
            'page_id' => $page->id,
            'user_id' => auth('web')->id(),
            'entity_name' => $blueprint->entity_name,
            'payload_json' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Saved successfully.'),
            'id' => $submission->id,
        ]);
    }

    public function records(Request $request): JsonResponse
    {
        if (!$this->aiTablesReady()) {
            return response()->json(['success' => true, 'rows' => []]);
        }

        $page = $this->resolvePageFromRequest($request);
        if (!$page) {
            return response()->json(['success' => false, 'message' => __('Target page not found.'), 'rows' => []], 404);
        }

        $rows = AiCustomPageSubmission::query()
            ->where('page_id', $page->id)
            ->latest('id')
            ->limit(min((int) $request->integer('limit', 20), 100))
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

    private function resolvePageFromRequest(Request $request): ?Page
    {
        $pageId = (int) $request->integer('page_id');
        if ($pageId > 0) {
            return Page::find($pageId);
        }

        $slug = trim((string) $request->input('page_slug', ''));
        if ($slug !== '') {
            return Page::where('slug', $slug)->first();
        }

        $referer = (string) $request->headers->get('referer', '');
        $path = trim((string) parse_url($referer, PHP_URL_PATH), '/');
        if ($path !== '') {
            return Page::where('slug', $path)->first();
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function sanitizeSubmittedValue($value): ?string
    {
        if (is_array($value)) {
            $value = implode(', ', array_map(fn ($v) => (string) $v, $value));
        }
        if (is_null($value)) {
            return null;
        }

        return trim(strip_tags((string) $value));
    }

    private function aiTablesReady(): bool
    {
        return Schema::hasTable('ai_custom_page_blueprints')
            && Schema::hasTable('ai_custom_page_submissions');
    }
}
