<?php

namespace App\Services\Ai;

/**
 * مرجع ثابت عن الموقع (لكل tenant) — يُخزَّن في static_options ويُحقَن في طلبات الـ AI.
 */
class AiSiteContextService
{
    /** مفتاح خيار static_options (يمكن تجاوزه من config/openai.php) */
    public static function optionKey(): string
    {
        return (string) config('openai.site_reference_option', 'ai_site_reference');
    }

    /**
     * النص الخام الذي أدخله المسؤول (بدون تعليمات إضافية).
     */
    public function getReferenceText(): string
    {
        return trim((string) get_static_option(self::optionKey(), ''));
    }

    public function hasReference(): bool
    {
        return $this->getReferenceText() !== '';
    }

    /**
     * جزء system message يُبنى من مرجع الموقع فقط (فارغ إذا لم يُعرَّف شيء).
     */
    public function getSystemInstructionBlock(): string
    {
        $ref = $this->getReferenceText();
        if ($ref === '') {
            return '';
        }

        $intro = __('The following is the official site profile. Always follow it when generating content (niche, audience, tone, products, policies, language preferences):');

        return trim($intro)."\n\n".$ref;
    }

    /**
     * دمج مرجع الموقع مع تعليمات إضافية (مثلاً: "اكتب مقالاً عن X").
     *
     * @return string|null  null إذا لم يوجد أي سياق
     */
    public function composeSystemMessage(?string $additional = null): ?string
    {
        $additional = $additional !== null ? trim($additional) : '';
        $block = $this->getSystemInstructionBlock();

        if ($block === '' && $additional === '') {
            return null;
        }

        if ($block === '') {
            return $additional !== '' ? $additional : null;
        }

        if ($additional === '') {
            return $block;
        }

        return $block."\n\n".__('Additional instructions for this request only:')."\n".$additional;
    }
}
