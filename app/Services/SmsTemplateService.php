<?php

namespace App\Services;

use App\DTOs\SmsTemplate\SmsTemplateDTO;
use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Validator;

class SmsTemplateService
{
    public function __construct(private SmsDriver $driver)
    {
    }

    /**
     * Get all SMS templates with optional filtering
     */
    public function getAllTemplates(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = SmsTemplate::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'ilike', '%'.$filters['search'].'%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderByDesc('id')->paginate(10);
    }

    /**
     * Get SMS template by ID
     */
    public function getTemplateById(int $id): SmsTemplate
    {
        return SmsTemplate::findOrFail($id);
    }

    /**
     * Get SMS template DTO by ID
     */
    public function getTemplateDTOById(int $id): SmsTemplateDTO
    {
        $template = SmsTemplate::findOrFail($id);

        return SmsTemplateDTO::fromModel($template);
    }

    /**
     * Create a new SMS template
     */
    public function createTemplate(array $data): SmsTemplate
    {
        $validatedData = Validator::make($data, [
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ])->validate();

        $validatedData['type'] = $data['type'] ?? 'student';

        return SmsTemplate::create($validatedData);
    }

    /**
     * Update SMS template
     */
    public function updateTemplate(int $id, array $data): SmsTemplate
    {
        $template = $this->getTemplateById($id);

        $validatedData = Validator::make($data, [
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ])->validate();

        $validatedData['type'] = $data['type'] ?? $template->type ?? 'student';

        $template->update($validatedData);

        // Reset to not_registered status and clear moderation fields
        $template->resetToNotRegistered();

        return $template->fresh();
    }

    /**
     * Delete SMS template
     */
    public function deleteTemplate(int $id): bool
    {
        $template = $this->getTemplateById($id);

        return $template->delete();
    }

    /**
     * Send template to moderation
     */
    public function sendToModeration(int $id): array
    {
        $template = $this->getTemplateById($id);

        // Validate template can be sent to moderation
        if ($template->status !== SmsTemplate::STATUS_NOT_REGISTERED) {
            throw new \InvalidArgumentException('Template must be in not_registered status to send to moderation');
        }

        // Validate template content
        $validator = Validator::make([
            'template' => $template->content,
            'sample' => $template->content,
        ], [
            'template' => 'required|string',
            'sample' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        $this->driver->submitTemplate($template);

        return [
            'success' => true,
            'message' => __('translation.sms.sent_to_moderation_success'),
            'template' => $template->fresh(),
        ];
    }

    /**
     * Get template statistics
     */
    public function getTemplateStatistics(): array
    {
        return [
            'total' => SmsTemplate::count(),
            'not_registered' => SmsTemplate::where('status', SmsTemplate::STATUS_NOT_REGISTERED)->count(),
            'moderation' => SmsTemplate::where('status', SmsTemplate::STATUS_MODERATION)->count(),
            'registered' => SmsTemplate::where('status', SmsTemplate::STATUS_REGISTERED)->count(),
        ];
    }

    /**
     * Check if template can be edited
     */
    public function canEditTemplate(int $id): bool
    {
        $template = $this->getTemplateById($id);

        return $template->status !== SmsTemplate::STATUS_MODERATION;
    }

    /**
     * Get available template types
     */
    public function getTemplateTypes(): array
    {
        return [
            'teacher' => __('translation.sms.type_teacher'),
            'student' => __('translation.sms.type_student'),
            'parent' => __('translation.sms.type_parent'),
        ];
    }

    /**
     * Get available template statuses
     */
    public function getTemplateStatuses(): array
    {
        return SmsTemplate::getStatuses();
    }

    /**
     * Check template status from external API
     */
    public function checkStatus(SmsTemplate $template): SmsTemplate
    {
        $this->driver->checkTemplate($template);

        return $template->fresh();
    }
}
