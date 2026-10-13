<?php

namespace App\Services;

use App\Models\SmsTemplate;
use Illuminate\Support\Collection;

class SmsBatchSnapshotService
{
    /**
     * @param  iterable<int, mixed>  $receivers
     * @return array<string, mixed>
     */
    public function build(
        iterable $receivers,
        SmsTemplate $template,
        string $sendSmsType,
        bool $sendToStudents,
        bool $sendToParents,
        ?string $scheduledAt,
        ?int $smsGroupId,
        ?int $branchId,
        ?int $createdById,
        string $status,
        string $source = 'manual',
        string $studyCenterName = '',
        string $studyCenterPhone = ''
    ): array {
        $receivers = $receivers instanceof Collection ? $receivers : collect($receivers);
        $eskizStatus = $this->normalizeEskizStatus($template->eskiz_status ?? 'service');

        $recipientCount = 0;
        $smsPartsCount = 0;
        $parts = [];
        $operatorCounts = [];
        $encodingCounts = [];

        foreach ($receivers as $receiver) {
            if ($sendToStudents) {
                $phone = $this->receiverValue($receiver, 'phone');
                if ($phone) {
                    $messageStats = $this->messageStats($template, $receiver, $sendSmsType, $phone, $studyCenterName, $studyCenterPhone);
                    $recipientCount++;
                    $smsPartsCount += $messageStats['parts'];
                    $parts[] = $messageStats['parts'];
                    $operatorCounts[$messageStats['operator']] = ($operatorCounts[$messageStats['operator']] ?? 0) + 1;
                    $encodingCounts[$messageStats['encoding']] = ($encodingCounts[$messageStats['encoding']] ?? 0) + 1;
                }
            }

            if ($sendToParents) {
                $phone = $this->receiverValue($receiver, 'parent_phone');
                if ($phone) {
                    $messageStats = $this->messageStats($template, $receiver, $sendSmsType, $phone, $studyCenterName, $studyCenterPhone);
                    $recipientCount++;
                    $smsPartsCount += $messageStats['parts'];
                    $parts[] = $messageStats['parts'];
                    $operatorCounts[$messageStats['operator']] = ($operatorCounts[$messageStats['operator']] ?? 0) + 1;
                    $encodingCounts[$messageStats['encoding']] = ($encodingCounts[$messageStats['encoding']] ?? 0) + 1;
                }
            }
        }

        return [
            'sms_group_id' => $smsGroupId,
            'sms_template_id' => $template->exists ? $template->id : null,
            'branch_id' => $branchId,
            'created_by_id' => $createdById,
            'source' => $source,
            'send_sms_type' => $sendSmsType,
            'recipient_scope' => $this->recipientScope($sendToStudents, $sendToParents),
            'status' => $status,
            'template_name' => $template->name,
            'template_content' => $template->content,
            'template_character_count' => mb_strlen($template->content ?? ''),
            'selected_receivers_count' => $receivers->count(),
            'recipient_count' => $recipientCount,
            'sms_parts_count' => $smsPartsCount,
            'encoding' => $this->dominantEncoding($encodingCounts),
            'sms_type' => $eskizStatus,
            'min_parts_per_message' => $parts !== [] ? min($parts) : 0,
            'max_parts_per_message' => $parts !== [] ? max($parts) : 0,
            'avg_parts_per_message' => $parts !== [] ? round(array_sum($parts) / count($parts), 1) : 0,
            'scheduled_at' => $scheduledAt,
            'meta' => [
                'send_to_students' => $sendToStudents,
                'send_to_parents' => $sendToParents,
                'eskiz_status' => $eskizStatus,
                'operator_counts' => $operatorCounts,
                'encoding_counts' => $encodingCounts,
            ],
        ];
    }

    /**
     * @return array{parts: int, operator: string, encoding: string}
     */
    private function messageStats(
        SmsTemplate $template,
        mixed $receiver,
        string $sendSmsType,
        string $phone,
        string $studyCenterName,
        string $studyCenterPhone
    ): array {
        $message = $this->replaceTemplateKeys($template->content, $receiver, $sendSmsType, $studyCenterName, $studyCenterPhone);
        $lengthMeta = $this->getSmsLengthUnits($message);
        $parts = $this->calculateSmsParts($lengthMeta['length'], $lengthMeta['encoding']);
        $operator = $this->detectOperator($phone);

        return [
            'parts' => $parts,
            'operator' => $operator,
            'encoding' => $lengthMeta['encoding'],
        ];
    }

    private function replaceTemplateKeys(
        string $template,
        mixed $receiver,
        string $sendSmsType,
        string $studyCenterName,
        string $studyCenterPhone
    ): string {
        $receiverName = $this->receiverName($receiver);
        $isStaff = $sendSmsType === 'staff';

        return str_replace(
            ['{STUDY_CENTER}', '{STUDENT}', '{TEACHER}', '{PHONE}'],
            [
                $studyCenterName,
                $isStaff ? '' : $receiverName,
                $isStaff ? $receiverName : '',
                $studyCenterPhone ? formattedPhone($studyCenterPhone) : '',
            ],
            $template
        );
    }

    private function receiverName(mixed $receiver): string
    {
        if (is_object($receiver) && method_exists($receiver, 'getFullName')) {
            return $receiver->getFullName();
        }

        $name = (string) ($this->receiverValue($receiver, 'name') ?? '');
        $surname = (string) ($this->receiverValue($receiver, 'surname') ?? '');

        return trim($name.' '.$surname);
    }

    private function receiverValue(mixed $receiver, string $key): mixed
    {
        if (is_array($receiver)) {
            return $receiver[$key] ?? null;
        }

        if (is_object($receiver)) {
            return $receiver->{$key} ?? null;
        }

        return null;
    }

    private function normalizeEskizStatus(string $eskizStatus): string
    {
        $eskizStatus = strtolower($eskizStatus);

        return in_array($eskizStatus, ['service', 'reklama'], true) ? $eskizStatus : 'service';
    }

    private function recipientScope(bool $sendToStudents, bool $sendToParents): string
    {
        if ($sendToStudents && $sendToParents) {
            return 'students_and_parents';
        }

        if ($sendToParents) {
            return 'parents';
        }

        return 'students';
    }

    private function detectOperator(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        $phone = preg_replace('/^998/', '', $phone) ?? '';

        if (strlen($phone) < 9) {
            return 'unknown';
        }

        return match (substr($phone, 0, 2)) {
            '97', '88', '87' => 'mobiuz',
            '93', '94', '50' => 'ucell',
            '90', '91' => 'beeline',
            '99', '77', '70', '95' => 'uzmobile',
            '33' => 'humans',
            '98' => 'perfectum',
            '20' => 'oq',
            default => 'unknown',
        };
    }

    /**
     * @return array{encoding: string, length: int}
     */
    private function getSmsLengthUnits(string $text): array
    {
        if (! $this->isGsm7($text)) {
            return ['encoding' => 'unicode', 'length' => mb_strlen($text)];
        }

        $extensionChars = ['^', '{', '}', '\\', '[', ']', '~', '|', '€'];
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $length = 0;

        foreach ($chars ?: [] as $char) {
            $length += in_array($char, $extensionChars, true) ? 2 : 1;
        }

        return ['encoding' => 'gsm7', 'length' => $length];
    }

    private function isGsm7(string $text): bool
    {
        $extensionChars = ['^', '{', '}', '\\', '[', ']', '~', '|', '€'];
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($chars)) {
            return false;
        }

        foreach ($chars as $char) {
            if ($char === "\n" || $char === "\r" || in_array($char, $extensionChars, true)) {
                continue;
            }

            if (preg_match('/^[\x20-\x7E]$/', $char) === 1) {
                continue;
            }

            return false;
        }

        return true;
    }

    private function calculateSmsParts(int $length, string $encoding): int
    {
        if ($length <= 0) {
            return 0;
        }

        if ($encoding === 'unicode') {
            return $length <= 70 ? 1 : (int) ceil($length / 67);
        }

        return $length <= 160 ? 1 : (int) ceil($length / 153);
    }

    /**
     * @param  array<string, int>  $encodingCounts
     */
    private function dominantEncoding(array $encodingCounts): ?string
    {
        if ($encodingCounts === []) {
            return null;
        }

        if (($encodingCounts['unicode'] ?? 0) > 0) {
            return 'unicode';
        }

        return 'gsm7';
    }
}
