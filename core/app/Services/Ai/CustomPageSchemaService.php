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
