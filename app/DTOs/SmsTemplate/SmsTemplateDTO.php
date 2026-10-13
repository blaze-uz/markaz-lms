<?php

namespace App\DTOs\SmsTemplate;

use App\DTOs\BaseDTO;
use App\Models\SmsTemplate;

class SmsTemplateDTO extends BaseDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $content = null,
        public ?string $type = null,
        public ?string $status = null,
        public ?string $eskiz_status = null,
        public ?string $eskiz_template_id = null,
        public ?string $message = null,
        public ?string $template = null,
        public ?string $sample = null,
        public ?string $sent_to_moderation_at = null,
        public ?string $moderation_response_at = null,
        public ?string $moderation_response = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            content: $data['content'] ?? null,
            type: $data['type'] ?? null,
            status: $data['status'] ?? null,
            eskiz_status: $data['eskiz_status'] ?? null,
            eskiz_template_id: $data['eskiz_template_id'] ?? null,
            message: $data['message'] ?? null,
            template: $data['template'] ?? null,
            sample: $data['sample'] ?? null,
            sent_to_moderation_at: $data['sent_to_moderation_at'] ?? null,
            moderation_response_at: $data['moderation_response_at'] ?? null,
            moderation_response: $data['moderation_response'] ?? null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
        );
    }

    /**
     * Create DTO from SmsTemplate model
     */
    public static function fromModel(SmsTemplate $template): self
    {
        return new self(
            id: $template->id,
            name: $template->name,
            content: $template->content,
            type: $template->type,
            status: $template->status,
            eskiz_status: $template->eskiz_status,
            eskiz_template_id: $template->eskiz_template_id,
            message: $template->message,
            template: $template->template,
            sample: $template->sample,
            sent_to_moderation_at: $template->sent_to_moderation_at?->toISOString(),
            moderation_response_at: $template->moderation_response_at?->toISOString(),
            moderation_response: $template->moderation_response,
            created_at: $template->created_at?->toISOString(),
            updated_at: $template->updated_at?->toISOString(),
        );
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'type' => $this->type,
            'status' => $this->status,
            'eskiz_status' => $this->eskiz_status,
            'eskiz_template_id' => $this->eskiz_template_id,
            'message' => $this->message,
            'template' => $this->template,
            'sample' => $this->sample,
            'sent_to_moderation_at' => $this->sent_to_moderation_at,
            'moderation_response_at' => $this->moderation_response_at,
            'moderation_response' => $this->moderation_response,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], fn($value) => $value !== null);
    }

    /**
     * Get only fillable fields for model
     */
    public function getFillable(): array
    {
        return array_filter([
            'name' => $this->name,
            'content' => $this->content,
            'type' => $this->type,
            'status' => $this->status,
            'eskiz_status' => $this->eskiz_status,
            'eskiz_template_id' => $this->eskiz_template_id,
            'message' => $this->message,
            'template' => $this->template,
            'sample' => $this->sample,
            'sent_to_moderation_at' => $this->sent_to_moderation_at,
            'moderation_response_at' => $this->moderation_response_at,
            'moderation_response' => $this->moderation_response,
        ], fn($value) => $value !== null);
    }

    /**
     * Check if template can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->status !== SmsTemplate::STATUS_MODERATION;
    }

    /**
     * Check if template can be sent to moderation
     */
    public function canBeSentToModeration(): bool
    {
        return $this->status === SmsTemplate::STATUS_NOT_REGISTERED;
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            SmsTemplate::STATUS_NOT_REGISTERED => __('translation.sms.status_not_registered'),
            SmsTemplate::STATUS_MODERATION => __('translation.sms.status_moderation'),
            SmsTemplate::STATUS_REGISTERED => __('translation.sms.status_registered'),
            default => $this->status ?? '',
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'teacher' => __('translation.sms.type_teacher'),
            'student' => __('translation.sms.type_student'),
            'parent' => __('translation.sms.type_parent'),
            default => $this->type ?? '',
        };
    }

    /**
     * Get Eskiz status label
     */
    public function getEskizStatusLabel(): string
    {
        return match ($this->eskiz_status) {
            'reklama' => __('translation.sms.eskiz_status_reklama'),
            'service' => __('translation.sms.eskiz_status_service'),
            default => $this->eskiz_status ?? '',
        };
    }
}
