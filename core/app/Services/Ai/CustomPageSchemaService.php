<?php

namespace App\Services\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CustomPageSchemaService
{
    public const CONTRACT = [
        'entity_name',
        'page_title',
        'page_summary',
        'fields',
        'sections',
        'actions',
        'dashboard_view',
        'ui_bindings',
    ];

    /**
     * @return array<string, mixed>
     */
    public function normalizeSchema(array $payload): array
    {
        $entity = Str::snake((string) ($payload['entity_name'] ?? 'custom_page_record'));
        $entity = preg_replace('/[^a-z0-9_]/', '', $entity) ?: 'custom_page_record';

        $fields = [];
        foreach ((array) ($payload['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $name = Str::snake((string) ($field['name'] ?? $field['key'] ?? ''));
            $label = trim((string) ($field['label'] ?? Str::title(str_replace('_', ' ', $name))));
            $type = (string) ($field['type'] ?? 'text');

            if ($name === '' || !preg_match('/^[a-z][a-z0-9_]{1,60}$/', $name)) {
                continue;
            }

            $fields[] = [
                'name' => $name,
                'label' => $label,
                'type' => in_array($type, ['text', 'email', 'number', 'date', 'datetime-local', 'tel', 'select', 'textarea'], true) ? $type : 'text',
                'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'options' => array_values(array_filter((array) ($field['options'] ?? []), fn ($v) => is_scalar($v))),
                'placeholder' => (string) ($field['placeholder'] ?? ''),
            ];
        }

        if ($fields === []) {
            $fields = [
                ['name' => 'full_name', 'label' => 'Full Name', 'type' => 'text', 'required' => true, 'options' => [], 'placeholder' => ''],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'options' => [], 'placeholder' => ''],
            ];
        }

        $actions = array_values(array_intersect(['create', 'list', 'update'], (array) ($payload['actions'] ?? ['create', 'list'])));
        if ($actions === []) {
            $actions = ['create', 'list'];
        }

        return [
            'entity_name' => $entity,
            'page_title' => trim((string) ($payload['page_title'] ?? 'Custom Page')),
            'page_summary' => trim((string) ($payload['page_summary'] ?? '')),
            'fields' => $fields,
            'sections' => array_values(array_filter((array) ($payload['sections'] ?? []), fn ($v) => is_array($v))),
            'actions' => $actions,
            'dashboard_view' => [
                'columns' => array_values(array_filter((array) Arr::get($payload, 'dashboard_view.columns', []), fn ($v) => is_scalar($v))),
                'default_sort' => (string) Arr::get($payload, 'dashboard_view.default_sort', 'latest'),
            ],
            'ui_bindings' => array_values(array_filter((array) ($payload['ui_bindings'] ?? []), fn ($v) => is_array($v))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildDefaultBindings(string $entityName): array
    {
        return [
            'entity_name' => $entityName,
            'routes' => [
                'create' => '/ai-custom-page/submit',
                'list' => '/ai-custom-page/records',
                'admin_list' => '/admin-home/pages/ai-submissions',
            ],
            'methods' => [
                'create' => 'POST',
                'list' => 'GET',
                'admin_list' => 'GET',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function requiredRoutes(): array
    {
        return [
            'tenant.frontend.ai_custom_page.submit',
            'tenant.frontend.ai_custom_page.records',
            'tenant.admin.pages.ai.submissions',
        ];
    }

    public function sanitizeRawHtml(string $html): string
    {
        // Drop script/style/iframe/object tags aggressively.
        $html = preg_replace('/<(script|style|iframe|object|embed)[^>]*>.*?<\/\\1>/is', '', $html) ?? $html;
        // Remove inline handlers like onclick=...
        $html = preg_replace('/\son[a-z]+\s*=\s*"[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace("/\son[a-z]+\s*=\s*'[^']*'/i", '', $html) ?? $html;
        $html = preg_replace('/\son[a-z]+\s*=\s*[^\s>]+/i', '', $html) ?? $html;

        return trim($html);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function extractFieldsFromHtml(string $html): array
    {
        $fields = [];
        if (preg_match_all('/<(input|select|textarea)\b([^>]*)>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tag = strtolower($match[1]);
                $attrs = $match[2];
                $name = $this->extractAttr($attrs, 'name');
                if (!$name || Str::startsWith($name, '_')) {
                    continue;
                }

                $type = $tag === 'input' ? strtolower($this->extractAttr($attrs, 'type') ?: 'text') : $tag;
                if ($type === 'submit' || $type === 'button' || $type === 'hidden') {
                    continue;
                }

                $fields[] = [
                    'name' => Str::snake($name),
                    'label' => Str::title(str_replace('_', ' ', $name)),
                    'type' => in_array($type, ['text', 'email', 'number', 'date', 'datetime-local', 'tel', 'select', 'textarea'], true) ? $type : 'text',
                    'required' => stripos($attrs, 'required') !== false,
                    'options' => [],
                    'placeholder' => (string) ($this->extractAttr($attrs, 'placeholder') ?? ''),
                ];
            }
        }

        return collect($fields)->unique('name')->values()->all();
    }

    public function renderStarterTemplate(array $schema): string
    {
        $fieldsHtml = '';
        foreach ($schema['fields'] as $field) {
            $name = e($field['name']);
            $label = e($field['label']);
            $type = e($field['type']);
            $placeholder = e($field['placeholder'] ?? '');
            $required = !empty($field['required']) ? 'required' : '';

            if ($type === 'textarea') {
                $fieldsHtml .= "<div class=\"ai-field\"><label>{$label}</label><textarea name=\"{$name}\" {$required} placeholder=\"{$placeholder}\"></textarea></div>";
            } elseif ($type === 'select') {
                $fieldsHtml .= "<div class=\"ai-field\"><label>{$label}</label><select name=\"{$name}\" {$required}></select></div>";
            } else {
                $fieldsHtml .= "<div class=\"ai-field\"><label>{$label}</label><input type=\"{$type}\" name=\"{$name}\" {$required} placeholder=\"{$placeholder}\" /></div>";
            }
        }

        return <<<HTML
<div class="ai-custom-page" data-ai-custom-page="1">
  <style>
    .ai-custom-page{max-width:920px;margin:20px auto;padding:24px;background:#fff;border:1px solid #e5e7eb;border-radius:16px}
    .ai-custom-page .ai-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .ai-custom-page .ai-field{display:flex;flex-direction:column;gap:6px}
    .ai-custom-page .ai-field input,.ai-custom-page .ai-field textarea,.ai-custom-page .ai-field select{height:44px;border:1px solid #d1d5db;border-radius:10px;padding:0 12px}
    .ai-custom-page .ai-field textarea{height:96px;padding-top:10px}
    .ai-custom-page .ai-btn{background:#10b981;border:0;color:#fff;border-radius:10px;padding:10px 16px;cursor:pointer}
    .ai-custom-page .ai-table{width:100%;border-collapse:collapse;margin-top:16px}
    .ai-custom-page .ai-table th,.ai-custom-page .ai-table td{border:1px solid #e5e7eb;padding:8px 10px}
  </style>
  <h2>{$schema['page_title']}</h2>
  <p>{$schema['page_summary']}</p>
  <form data-ai-custom-form="1">
    <div class="ai-grid">{$fieldsHtml}</div>
    <div style="margin-top:14px"><button class="ai-btn" type="submit">Submit</button></div>
  </form>
  <div style="margin-top:20px">
    <h3>Latest records</h3>
    <table class="ai-table">
      <thead><tr><th>#</th><th>Data</th><th>Date</th></tr></thead>
      <tbody data-ai-custom-list="1"></tbody>
    </table>
  </div>
</div>
HTML;
    }

    /**
     * Prompt-aware template so structured mode is not static.
     */
    public function renderPromptAwareTemplate(array $schema, string $prompt = '', string $lang = 'en'): string
    {
        $text = mb_strtolower($prompt.' '.$schema['page_title'].' '.$schema['page_summary']);
        $isRtl = str_contains($text, 'arabic') || str_contains($text, 'rtl') || preg_match('/[\x{0600}-\x{06FF}]/u', $text);

        $title = e((string) ($schema['page_title'] ?? 'Custom Landing Page'));
        $summary = e((string) ($schema['page_summary'] ?? ''));
        $cta = $isRtl ? 'اطلب الآن' : 'Order Now';

        $palette = ['#0f172a', '#f5f1e8', '#c8a96b'];
        if (str_contains($text, 'green')) {
            $palette = ['#064e3b', '#ecfdf5', '#10b981'];
        } elseif (str_contains($text, 'blue')) {
            $palette = ['#0b2447', '#eef4ff', '#2563eb'];
        }

        $gallery = '';
        if (str_contains($text, 'gallery') || str_contains($text, 'image') || str_contains($text, 'صور')) {
            $gallery = '
            <section class="ai-section">
              <h3>'.($isRtl ? 'صور المنتج' : 'Product Gallery').'</h3>
              <div class="ai-gallery">
                <div class="ai-img">Image 1</div><div class="ai-img">Image 2</div><div class="ai-img">Image 3</div><div class="ai-img">Image 4</div>
              </div>
            </section>';
        }

        $features = '';
        if (str_contains($text, 'feature') || str_contains($text, 'مميزات')) {
            $features = '
            <section class="ai-section">
              <h3>'.($isRtl ? 'مميزات المنتج' : 'Features').'</h3>
              <div class="ai-cards">
                <article class="ai-card">Feature 1</article>
                <article class="ai-card">Feature 2</article>
                <article class="ai-card">Feature 3</article>
                <article class="ai-card">Feature 4</article>
              </div>
            </section>';
        }

        $faq = '';
        if (str_contains($text, 'faq') || str_contains($text, 'الاسئلة') || str_contains($text, 'الأسئلة')) {
            $faq = '
            <section class="ai-section">
              <h3>FAQ</h3>
              <details><summary>Question 1</summary><p>Answer</p></details>
              <details><summary>Question 2</summary><p>Answer</p></details>
            </section>';
        }

        $fieldsHtml = '';
        foreach ((array) ($schema['fields'] ?? []) as $field) {
            $name = e((string) ($field['name'] ?? 'field'));
            $label = e((string) ($field['label'] ?? $name));
            $type = e((string) ($field['type'] ?? 'text'));
            $placeholder = e((string) ($field['placeholder'] ?? ''));
            $required = !empty($field['required']) ? 'required' : '';

            if ($type === 'textarea') {
                $fieldsHtml .= "<div class=\"ai-field\"><label>{$label}</label><textarea name=\"{$name}\" {$required} placeholder=\"{$placeholder}\"></textarea></div>";
            } elseif ($type === 'select') {
                $fieldsHtml .= "<div class=\"ai-field\"><label>{$label}</label><select name=\"{$name}\" {$required}></select></div>";
            } else {
                $fieldsHtml .= "<div class=\"ai-field\"><label>{$label}</label><input type=\"{$type}\" name=\"{$name}\" {$required} placeholder=\"{$placeholder}\"/></div>";
            }
        }

        $dir = $isRtl ? 'rtl' : 'ltr';
        $align = $isRtl ? 'right' : 'left';

        return <<<HTML
<div class="ai-custom-page ai-theme" data-ai-custom-page="1" dir="{$dir}">
  <style>
    .ai-theme{max-width:1080px;margin:24px auto;padding:24px;border-radius:18px;background:{$palette[1]};border:1px solid rgba(15,23,42,.08);text-align:{$align};font-family:Inter,system-ui,sans-serif}
    .ai-hero{background:linear-gradient(135deg,{$palette[0]} 0%, #1f2937 60%);color:#fff;padding:26px;border-radius:14px}
    .ai-hero .ai-btn{display:inline-block;background:{$palette[2]};color:#111827;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:700}
    .ai-section{margin-top:20px}
    .ai-gallery{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
    .ai-img{height:110px;border:1px dashed #94a3b8;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#fff}
    .ai-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
    .ai-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px}
    .ai-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .ai-field{display:flex;flex-direction:column;gap:6px}
    .ai-field input,.ai-field textarea,.ai-field select{height:44px;border:1px solid #d1d5db;border-radius:10px;padding:0 12px;background:#fff}
    .ai-field textarea{height:96px;padding-top:10px}
    .ai-submit{margin-top:12px;background:{$palette[0]};color:#fff;border:0;border-radius:10px;padding:10px 16px}
    .ai-table{width:100%;border-collapse:collapse;margin-top:10px;background:#fff}
    .ai-table th,.ai-table td{border:1px solid #e5e7eb;padding:8px}
    @media (max-width: 900px){.ai-gallery,.ai-cards,.ai-grid{grid-template-columns:1fr 1fr}}
    @media (max-width: 640px){.ai-gallery,.ai-cards,.ai-grid{grid-template-columns:1fr}}
  </style>

  <section class="ai-hero">
    <h2>{$title}</h2>
    <p>{$summary}</p>
    <a href="#ai-order-form" class="ai-btn">{$cta}</a>
  </section>

  {$gallery}
  {$features}
  {$faq}

  <section class="ai-section" id="ai-order-form">
    <h3>{$cta}</h3>
    <form data-ai-custom-form="1">
      <div class="ai-grid">{$fieldsHtml}</div>
      <button type="submit" class="ai-submit">{$cta}</button>
    </form>
  </section>

  <section class="ai-section">
    <h3>Latest records</h3>
    <table class="ai-table">
      <thead><tr><th>#</th><th>Data</th><th>Date</th></tr></thead>
      <tbody data-ai-custom-list="1"></tbody>
    </table>
  </section>
</div>
HTML;
    }

    /**
     * Try to extract full renderable HTML from model JSON payload.
     *
     * @param array<string, mixed> $payload
     */
    public function extractRenderableHtml(array $payload): ?string
    {
        $uiBindings = (array) ($payload['ui_bindings'] ?? []);
        foreach ($uiBindings as $binding) {
            if (!is_array($binding)) {
                continue;
            }

            foreach (['render_html', 'html', 'template_html', 'page_html'] as $key) {
                $html = trim((string) ($binding[$key] ?? ''));
                if ($html !== '' && str_contains($html, '<')) {
                    return $this->sanitizeRawHtml($html);
                }
            }
        }

        return null;
    }

    public function buildSystemPrompt(string $mode, string $lang): string
    {
        $contract = implode(',', self::CONTRACT);

        return implode("\n", [
            'You are an expert SaaS page architect and frontend/backend planner.',
            'Return one valid JSON object only. No markdown.',
            'JSON keys must include exactly: '.$contract,
            'fields entries must include: name,label,type,required,placeholder,options.',
            'Allowed field types: text,email,number,date,datetime-local,tel,select,textarea.',
            'actions must include create and list when possible.',
            'To avoid generic repeated output, add one ui_bindings item containing a full custom page HTML in key render_html (with inline CSS, semantic sections, no JS).',
            'The render_html should reflect the user prompt style, colors, and page sections.',
            'Do not include executable javascript.',
            'Language locale hint: '.$lang,
            'Mode: '.$mode,
        ]);
    }

    private function extractAttr(string $attrs, string $name): ?string
    {
        if (preg_match('/\b'.$name.'\s*=\s*"([^"]*)"/i', $attrs, $m)) {
            return trim($m[1]);
        }
        if (preg_match("/\b{$name}\s*=\s*'([^']*)'/i", $attrs, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/\b'.$name.'\s*=\s*([^\s>]+)/i', $attrs, $m)) {
            return trim($m[1], "\"'");
        }

        return null;
    }
}
