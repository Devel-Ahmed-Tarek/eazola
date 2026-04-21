<?php

namespace Tests\Unit;

use App\Services\Ai\CustomPageSchemaService;
use PHPUnit\Framework\TestCase;

class CustomPageSchemaServiceTest extends TestCase
{
    public function test_normalize_schema_keeps_required_contract_keys(): void
    {
        $service = new CustomPageSchemaService();
        $schema = $service->normalizeSchema([
            'entity_name' => 'Booking Form',
            'page_title' => 'Book Appointment',
            'fields' => [
                ['name' => 'Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'type' => 'email'],
            ],
            'actions' => ['create', 'list'],
        ]);

        $this->assertSame('booking_form', $schema['entity_name']);
        $this->assertArrayHasKey('fields', $schema);
        $this->assertCount(2, $schema['fields']);
        $this->assertSame('full_name', $schema['fields'][0]['name']);
    }

    public function test_sanitize_raw_html_removes_scripts_and_inline_handlers(): void
    {
        $service = new CustomPageSchemaService();
        $raw = '<form onsubmit="alert(1)"><input name="email" /><script>alert(2)</script></form>';
        $sanitized = $service->sanitizeRawHtml($raw);

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('onsubmit=', $sanitized);
        $this->assertStringContainsString('<form', $sanitized);
    }

    public function test_extract_fields_from_html_detects_inputs(): void
    {
        $service = new CustomPageSchemaService();
        $html = '<form><input type="text" name="full_name"><input type="email" name="email"><textarea name="message"></textarea></form>';
        $fields = $service->extractFieldsFromHtml($html);

        $this->assertCount(3, $fields);
        $this->assertSame('full_name', $fields[0]['name']);
        $this->assertSame('email', $fields[1]['name']);
        $this->assertSame('textarea', $fields[2]['type']);
    }
}
