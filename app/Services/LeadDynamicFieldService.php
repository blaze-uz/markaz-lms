<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeadDynamicFieldService
{
    public const SETTINGS_KEY = 'lead_dynamic_fields';

    public const ALLOWED_TYPES = ['text', 'textarea', 'number', 'select'];

    /**
     * @return array<int, array{key: string, label: string, type: string, required: bool, options: array<int, string>}>
     */
    public function definitions(): array
    {
        $row = Settings::query()->where('key', self::SETTINGS_KEY)->first();
        if (! $row || $row->value === null || $row->value === '') {
            return [];
        }

        $decoded = json_decode($row->value, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $this->sanitizeDefinitions($decoded);
    }

    /**
     * @param  array<string, mixed>  $definitionsPayload
     * @return array<int, array{key: string, label: string, type: string, required: bool, options: array<int, string>}>
     */
    public function validateAndEncodeDefinitions(array $definitionsPayload): string
    {
        $validator = Validator::make(
            ['dynamic_fields' => $definitionsPayload],
            [
                'dynamic_fields' => ['nullable', 'array'],
                'dynamic_fields.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
                'dynamic_fields.*.label' => ['required', 'string', 'max:255'],
                'dynamic_fields.*.type' => ['required', 'string', Rule::in(self::ALLOWED_TYPES)],
                'dynamic_fields.*.required' => ['sometimes', 'boolean'],
                'dynamic_fields.*.options_raw' => ['nullable', 'string', 'max:5000'],
            ]
        );

        $validator->after(function ($validator): void {
            /** @var array<int, array<string, mixed>>|null $fields */
            $fields = $validator->getData()['dynamic_fields'] ?? [];
            if (! is_array($fields)) {
                return;
            }

            $keys = collect($fields)->pluck('key')->filter();
            if ($keys->count() !== $keys->unique()->count()) {
                $validator->errors()->add('dynamic_fields', 'Each field key must be unique.');
            }

            foreach ($fields as $index => $field) {
                if (($field['type'] ?? '') === 'select') {
                    $options = $this->parseOptionsRaw($field['options_raw'] ?? '');
                    if (count($options) < 1) {
                        $validator->errors()->add("dynamic_fields.$index.options_raw", __('validation.required', ['attribute' => 'options']));
                    }
                }
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        /** @var array<int, array<string, mixed>> $fields */
        $fields = $validator->validated()['dynamic_fields'] ?? [];

        $normalized = [];
        foreach ($fields as $field) {
            $type = $field['type'];
            $options = $type === 'select'
                ? $this->parseOptionsRaw($field['options_raw'] ?? '')
                : [];

            $normalized[] = [
                'key' => $field['key'],
                'label' => $field['label'],
                'type' => $type,
                'required' => (bool) ($field['required'] ?? false),
                'options' => $options,
            ];
        }

        return json_encode($normalized);
    }

    /**
     * Validation rules for custom_attributes on Lead create/update.
     *
     * @return array<string, list<string|\Illuminate\Contracts\Validation\Rule>>
     */
    public function customAttributeValidationRules(): array
    {
        $definitions = $this->definitions();
        if ($definitions === []) {
            return [
                'custom_attributes' => ['nullable', 'array'],
            ];
        }

        $rules = [
            'custom_attributes' => ['nullable', 'array'],
        ];

        foreach ($definitions as $def) {
            $key = $def['key'];
            $name = "custom_attributes.$key";
            $base = [];

            if ($def['required']) {
                $base[] = 'required';
            } else {
                $base[] = 'nullable';
            }

            switch ($def['type']) {
                case 'number':
                    $base[] = 'numeric';
                    break;
                case 'select':
                    $options = $def['options'];
                    $base[] = Rule::in($options);
                    break;
                case 'textarea':
                    $base[] = 'string';
                    $base[] = 'max:5000';
                    break;
                case 'text':
                default:
                    $base[] = 'string';
                    $base[] = 'max:2000';
                    break;
            }

            $rules[$name] = $base;
        }

        return $rules;
    }

    /**
     * Strip unknown keys; ensure all defined keys exist (null when optional and missing).
     *
     * @param  array<string, mixed>|null  $customAttributes
     * @return array<string, mixed>|null
     */
    public function normalizeValidated(?array $customAttributes): ?array
    {
        $definitions = $this->definitions();
        if ($definitions === []) {
            return null;
        }

        $input = is_array($customAttributes) ? $customAttributes : [];
        $out = [];

        foreach ($definitions as $def) {
            $key = $def['key'];
            if (! array_key_exists($key, $input)) {
                $out[$key] = null;

                continue;
            }

            $value = $input[$key];
            if ($value === '' || $value === null) {
                $out[$key] = null;

                continue;
            }

            if ($def['type'] === 'number') {
                $out[$key] = is_numeric($value) ? 0 + $value : null;
            } else {
                $out[$key] = is_string($value) ? $value : (string) $value;
            }
        }

        return $out;
    }

    /**
     * @param  array<int, mixed>  $decoded
     * @return array<int, array{key: string, label: string, type: string, required: bool, options: array<int, string>}>
     */
    private function sanitizeDefinitions(array $decoded): array
    {
        $out = [];
        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }
            $key = isset($row['key']) && is_string($row['key']) ? $row['key'] : '';
            if ($key === '' || ! preg_match('/^[a-z0-9_]+$/', $key)) {
                continue;
            }
            $label = isset($row['label']) && is_string($row['label']) ? $row['label'] : $key;
            $type = isset($row['type']) && is_string($row['type']) ? $row['type'] : 'text';
            if (! in_array($type, self::ALLOWED_TYPES, true)) {
                $type = 'text';
            }
            $required = ! empty($row['required']);
            $options = [];
            if (isset($row['options']) && is_array($row['options'])) {
                foreach ($row['options'] as $opt) {
                    if (is_string($opt) && $opt !== '') {
                        $options[] = $opt;
                    }
                }
            }

            $out[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'required' => $required,
                'options' => $options,
            ];
        }

        return $out;
    }

    private function parseOptionsRaw(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $options = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $options[] = $line;
            }
        }

        return $options;
    }

    /**
     * @param  Builder<Lead>  $query
     * @param  array<string, mixed>  $input
     */
    public function applyTableCustomAttributeFilters(Builder $query, array $input): void
    {
        if ($input === []) {
            return;
        }

        $definitions = collect($this->definitions())->keyBy('key');

        foreach ($input as $key => $value) {
            if (! is_string($key) || ! preg_match('/^[a-z0-9_]+$/', $key)) {
                continue;
            }

            $def = $definitions->get($key);
            if ($def === null) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === '') {
                continue;
            }

            $type = $def['type'];

            if ($type === 'text' || $type === 'textarea') {
                $like = '%'.addcslashes((string) $value, '%_\\').'%';
                $query->whereRaw('(custom_attributes ->> ?) ilike ?', [$key, $like]);
            } elseif ($type === 'number') {
                if (! is_numeric($value)) {
                    continue;
                }
                $canonical = (string) ($value + 0);
                $query->whereRaw('(custom_attributes ->> ?) = ?', [$key, $canonical]);
            } elseif ($type === 'select') {
                $str = (string) $value;
                $allowed = $def['options'] ?? [];
                if (! in_array($str, $allowed, true)) {
                    continue;
                }
                $query->whereRaw('(custom_attributes ->> ?) = ?', [$key, $str]);
            }
        }
    }
}
