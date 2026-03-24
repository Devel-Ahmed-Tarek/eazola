<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
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

        $lang = $validated['lang'] ?? app()->getLocale();
        $categoryMap = FaqCategory::query()->get()->map(function (FaqCategory $cat) use ($lang) {
            return ['id' => (int) $cat->id, 'title' => (string) $cat->getTranslation('title', $lang)];
        })->values()->all();

        if (empty($categoryMap)) {
            $fallback = new FaqCategory();
            $fallback->setTranslation('title', $lang, SanitizeInput::esc_html(__('General')));
            $fallback->status = 1;
            $fallback->save();
            $categoryMap = [[
                'id' => (int) $fallback->id,
                'title' => (string) $fallback->getTranslation('title', $lang),
            ]];
        }

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

            $payload = $this->decodeJson($result->content);
            $validIds = collect($categoryMap)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $payload['category_id'], $validIds, true)) {
                $payload['category_id'] = (int) ($validIds[0] ?? 0);
            }

            return response()->json(['success' => true] + $payload);
        } catch (OpenAIServiceException $e) {
            Log::warning('FAQ AI assist failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('FAQ AI assist exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('AI could not produce valid FAQ content. Try again.')], 502);
        }
    }

    /**
     * @return array{title:string,description:string,category_id:int}
     */
    private function decodeJson(string $raw): array
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
}

