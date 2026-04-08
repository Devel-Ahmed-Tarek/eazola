<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Models\MediaUploader;
use App\Models\Testimonial;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestimonialAiAssistantController extends Controller
{
    public function assist(Request $request, OpenAIChatService $openai): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'required|string|in:generate,refine',
            'lang' => 'nullable|string|max:20',
            'all_languages' => 'nullable|boolean',
            'testimonial_id' => 'nullable|integer|exists:testimonials,id',
            'topic' => 'nullable|string|max:4000',
            'instruction' => 'nullable|string|max:4000',
            'current_name' => 'nullable|string|max:500',
            'current_designation' => 'nullable|string|max:500',
            'current_company' => 'nullable|string|max:500',
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
        $fallbackImageId = (int) (MediaUploader::query()->whereNotNull('path')->orderByDesc('id')->value('id') ?? 0);

        $system = implode("\n", [
            'You are a testimonial writing assistant.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON keys must be exactly: "name","designation","company","description","image_id".',
            'Keep text concise and marketing-friendly.',
            'description should be short and natural testimonial text.',
            'Write in language/locale hint: '.$lang,
            'If no image inferred, use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic for the testimonial.')], 422);
                }
                $user = "Generate one testimonial.\n\nTopic/brief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to improve the testimonial.')], 422);
                }
                $user = "Current testimonial:\nName: ".($validated['current_name'] ?? '')
                    ."\nDesignation: ".($validated['current_designation'] ?? '')
                    ."\nCompany: ".($validated['current_company'] ?? '')
                    ."\nDescription: ".($validated['current_description'] ?? '')
                    ."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.6,
                'max_tokens' => 1600,
            ]);

            $payload = $this->decodeSingleJson($result->content);
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = $fallbackImageId;
            }

            return response()->json(['success' => true, 'all_languages' => false] + $payload);
        } catch (OpenAIServiceException $e) {
            Log::warning('Testimonial AI assist failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Testimonial AI assist exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('AI could not produce valid testimonial content. Try again.')], 502);
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
            'You are a multilingual testimonial assistant.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON must contain keys: "image_id","translations".',
            'translations must include all locale slugs: '.implode(', ', $langSlugs),
            'Each locale translation must include: "name","designation","company","description".',
            'Keep wording concise and natural.',
            'If no image inferred, use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic for the testimonial.')], 422);
                }
                $user = "Generate one testimonial in ALL locales.\n\nTopic/brief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to improve the testimonial.')], 422);
                }
                $id = (int) ($validated['testimonial_id'] ?? 0);
                if ($id <= 0) {
                    return response()->json(['success' => false, 'message' => __('To improve all languages, save the testimonial first then edit it.')], 422);
                }
                $item = Testimonial::find($id);
                if (! $item) {
                    return response()->json(['success' => false, 'message' => __('Testimonial item not found.')], 404);
                }

                $perLang = [];
                foreach ($langSlugs as $slug) {
                    $perLang[$slug] = [
                        'name' => (string) $item->getTranslation('name', $slug),
                        'designation' => (string) $item->getTranslation('designation', $slug),
                        'company' => (string) $item->getTranslation('company', $slug),
                        'description' => (string) $item->getTranslation('description', $slug),
                    ];
                }

                $user = "Current testimonial by locale (JSON):\n".json_encode($perLang, JSON_UNESCAPED_UNICODE)
                    ."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.6,
                'max_tokens' => 3200,
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
            Log::warning('Testimonial AI all-language assist failed', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Testimonial AI all-language assist exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => __('AI could not produce valid testimonial content. Try again.')], 502);
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
            'name' => (string) ($data['name'] ?? ''),
            'designation' => (string) ($data['designation'] ?? ''),
            'company' => (string) ($data['company'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
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
                'name' => (string) ($t['name'] ?? ''),
                'designation' => (string) ($t['designation'] ?? ''),
                'company' => (string) ($t['company'] ?? ''),
                'description' => (string) ($t['description'] ?? ''),
            ];
        }

        return [
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
            'translations' => $out,
        ];
    }
}

