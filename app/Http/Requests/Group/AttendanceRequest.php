<?php

namespace App\Http\Requests\Group;

use App\Http\Requests\BaseRequest;
use App\Models\ValidationSettings;

class AttendanceRequest extends BaseRequest
{
    public function rules(): array
    {
        $fields = ValidationSettings::where('group', 'attendance')->get();
        $additionalRules = [];

        foreach ($fields as $field) {
            if (str_contains($field->name, '.*')) {
                $fieldBaseName = substr($field->name, 0, -2);
                $additionalRules[$fieldBaseName.'.*'] = $field->required ? 'required' : 'sometimes';
            } else {
                if ($field->name == 'comment') {
                    $additionalRules[$field->name.'.*'] = $field->required ? 'required_if:status.*,absent' : 'sometimes';
                } elseif ($field->name == 'score') {
                    $additionalRules[$field->name.'.*'] = $field->required ? 'required_if:status.*,present' : 'sometimes';
                } elseif ($field->name == 'homework') {
                    $additionalRules[$field->name.'.*'] = $field->required ? 'required|boolean' : 'sometimes|boolean';
                } else {
                    $additionalRules[$field->name.'.*'] = $field->required ? 'required' : 'sometimes';
                }
            }
        }

        $rules = [
            'status' => 'required',
            'date' => 'sometimes',
            'theme' => 'nullable|string',
        ];

        return array_merge($rules, $additionalRules);
    }

    public function messages()
    {
        return [
            'status.required' => 'Status maydoni to\'ldirilishi shart',
            'comment.*.required_if' => 'Komentariya maydoni to\'ldirilishi shart, agar o\'quvchi kelmagan bo\'lsa',
            'score.*.required_if' => 'Ball maydoni to\'ldirilishi shart',
            'homework.*.required' => 'Uy vazifasi maydoni to\'ldirilishi shart',
            'homework.*.boolean' => 'Uy vazifasi maydoni boolean formatida bo\'lishi kerak',
            'date.date' => 'Sana maydoni sana formatida bo\'lishi kerak',
            'date.date_format' => 'Sana maydoni sana formatida bo\'lishi kerak',
        ];
    }
}
