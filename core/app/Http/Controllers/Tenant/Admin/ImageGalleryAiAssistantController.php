<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Models\ImageGallery;
use App\Models\ImageGalleryCategory;
use App\Models\MediaUploader;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use App\Services\Ai\OpenAIChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageGalleryAiAssistantController extends Controller
{
    public function assist(Request $request, OpenAIChatService $openai): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'required|string|in:generate,refine',
            'lang' => 'nullable|string|max:20',
            'all_languages' => 'nullable|boolean',
            'gallery_id' => 'nullable|integer|exists:image_galleries,id',
            'topic' => 'nullable|string|max:4000',
            'instruction' => 'nullable|string|max:4000',
            'current_title' => 'nullable|string|max:500',
            'current_subtitle' => 'nullable|string|max:2000',
        ]);

        if (! $openai->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('OpenAI API is not configured. Add OPENAI_API_KEY in .env and clear config cache.'),
            ], 422);
        }

        if (! ImageGalleryCategory::query()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('Create at least one image gallery category first.'),
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
        $categoryHint = $this->categoryPromptHint();

        $system = implode("\n", [
            'You are an image gallery item assistant.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON keys must be exactly: "title","subtitle","category_id","image_id".',
            'title is a short gallery item title; subtitle is a one-line caption or short description.',
            $categoryHint,
            'Write in language/locale hint: '.$lang,
            'If no image inferred, use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or brief for the gallery item.')], 422);
                }
                $user = "Generate one gallery item (title + subtitle).\n\nTopic/brief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to improve the content.')], 422);
                }
                $user = "Current gallery item:\nTitle: ".($validated['current_title'] ?? '')
                    ."\nSubtitle: ".($validated['current_subtitle'] ?? '')
                    ."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.55,
                'max_tokens' => 1400,
            ]);

            $payload = $this->decodeSingleJson($result->content);
            $payload['category_id'] = $this->normalizeCategoryId((int) ($payload['category_id'] ?? 0));
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = $fallbackImageId;
            }

            return response()->json(['success' => true, 'all_languages' => false] + $payload);
        } catch (OpenAIServiceException $e) {
            Log::warning('Image gallery AI assist failed', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Image gallery AI assist exception', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('AI could not produce valid gallery content. Try again.')], 502);
        }
    }

    protected function assistAllLanguages(array $validated, OpenAIChatService $openai): JsonResponse
    {
        $langSlugs = GlobalLanguage::all_languages(1)->pluck('slug')->filter()->values()->all();
        if ($langSlugs === []) {
            $langSlugs = [app()->getLocale()];
        }

        $fallbackImageId = (int) (MediaUploader::query()->whereNotNull('path')->orderByDesc('id')->value('id') ?? 0);
        $categoryHint = $this->categoryPromptHint();

        $system = implode("\n", [
            'You are a multilingual image gallery assistant.',
            'Return ONE valid JSON object only, no markdown.',
            'JSON must contain keys: "image_id","category_id","translations".',
            'translations must include all locale slugs: '.implode(', ', $langSlugs),
            'Each locale translation must include: "title","subtitle".',
            $categoryHint,
            'If no image inferred, use image_id='.$fallbackImageId.'.',
        ]);

        try {
            if ($validated['mode'] === 'generate') {
                $topic = trim((string) ($validated['topic'] ?? ''));
                if ($topic === '') {
                    return response()->json(['success' => false, 'message' => __('Please enter a topic or brief for the gallery item.')], 422);
                }
                $user = "Generate one gallery item in ALL locales.\n\nTopic/brief:\n".$topic;
            } else {
                $instruction = trim((string) ($validated['instruction'] ?? ''));
                if ($instruction === '') {
                    return response()->json(['success' => false, 'message' => __('Please describe how you want to improve the content.')], 422);
                }
                $id = (int) ($validated['gallery_id'] ?? 0);
                if ($id <= 0) {
                    return response()->json(['success' => false, 'message' => __('To improve all languages, save the gallery item first then edit it.')], 422);
                }
                $item = ImageGallery::find($id);
                if (! $item) {
                    return response()->json(['success' => false, 'message' => __('Gallery item not found.')], 404);
                }

                $perLang = [];
                foreach ($langSlugs as $slug) {
                    $perLang[$slug] = [
                        'title' => (string) $item->getTranslation('title', $slug),
                        'subtitle' => (string) $item->getTranslation('subtitle', $slug),
                    ];
                }

                $user = "Current gallery item by locale (JSON):\n".json_encode($perLang, JSON_UNESCAPED_UNICODE)
                    ."\n\nRequested update:\n".$instruction;
            }

            $result = $openai->chatWithSiteReference($user, $system, null, [
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.55,
                'max_tokens' => 2800,
            ]);

            $payload = $this->decodeAllLangJson($result->content, $langSlugs);
            $payload['category_id'] = $this->normalizeCategoryId((int) ($payload['category_id'] ?? 0));
            if ((int) $payload['image_id'] <= 0) {
                $payload['image_id'] = $fallbackImageId;
            }

            return response()->json([
                'success' => true,
                'all_languages' => true,
                'image_id' => $payload['image_id'],
                'category_id' => $payload['category_id'],
                'translations' => $payload['translations'],
            ]);
        } catch (OpenAIServiceException $e) {
            Log::warning('Image gallery AI all-language assist failed', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            Log::error('Image gallery AI all-language assist exception', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('AI could not produce valid gallery content. Try again.')], 502);
        }
    }

    private function categoryPromptHint(): string
    {
        $def = GlobalLanguage::default_slug();
        $pairs = [];
        $ids = [];
        foreach (ImageGalleryCategory::query()->orderBy('id')->get() as $cat) {
            $ids[] = (string) $cat->id;
            $pairs[] = $cat->id.'='.SanitizeInput::esc_html((string) $cat->getTranslation('title', $def));
        }
        if ($pairs === []) {
            return 'If no categories exist, use category_id=0.';
        }

        return 'category_id MUST be one of these ids: '.implode(', ', $ids).'. Mapping: '.implode('; ', $pairs).'.';
    }

    private function normalizeCategoryId(int $id): int
    {
        if ($id <= 0) {
            $first = ImageGalleryCategory::query()->orderBy('id')->value('id');

            return (int) ($first ?? 0);
        }
        $exists = ImageGalleryCategory::query()->where('id', $id)->exists();

        return $exists ? $id : (int) (ImageGalleryCategory::query()->orderBy('id')->value('id') ?? 0);
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
            'subtitle' => (string) ($data['subtitle'] ?? ''),
            'category_id' => max(0, (int) ($data['category_id'] ?? 0)),
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
                'title' => (string) ($t['title'] ?? ''),
                'subtitle' => (string) ($t['subtitle'] ?? ''),
            ];
        }

        return [
            'image_id' => max(0, (int) ($data['image_id'] ?? 0)),
            'category_id' => max(0, (int) ($data['category_id'] ?? 0)),
            'translations' => $out,
        ];
    }
}
