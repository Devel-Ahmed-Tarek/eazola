<?php

namespace Modules\Attributes\Http\Requests\Concerns;

trait NormalizesAttributeRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('image_id')) {
            $normalized['image_id'] = $this->normalizeNullableId($this->input('image_id'));
        }

        foreach (['category_id', 'sub_category_id', 'status_id'] as $field) {
            if ($this->has($field)) {
                $normalized[$field] = $this->normalizeRequiredId($this->input($field));
            }
        }

        $this->merge($normalized);
    }

    protected function normalizeNullableId(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 'null') {
            return null;
        }

        if (is_array($value)) {
            $value = $value['id'] ?? $value['value'] ?? null;
        }

        if (is_string($value)) {
            if (str_contains($value, '[object') || str_contains($value, 'Object')) {
                return null;
            }

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function normalizeRequiredId(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (is_array($value)) {
            $value = $value['id'] ?? $value['value'] ?? null;
        }

        if (is_string($value) && !is_numeric($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : $value;
    }
}
