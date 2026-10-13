<?php

namespace App\Livewire\Sms;

use App\Models\SmsTemplate;
use Livewire\Component;

class SendSms extends Component
{
    public $receivers = [];

    public $sendSmsType;

    public $selectedTemplate;

    public $selectedTemplateId;

    public $showStudents = true;

    public $showParents = false;

    public $scheduleStatus = false;

    public $templates;

    public $previewData = [];

    public $previewStats = [];

    public $currentPage = 1;

    public $perPage = 20;

    public $showPreviewModal = false;

    private ?string $studyCenterName = null;

    private ?string $studyCenterPhone = null;

    public function mount($receivers, $sendSmsType = null)
    {
        $this->receivers = $receivers;
        $this->sendSmsType = $sendSmsType;
        $this->loadTemplates();
        $this->studyCenterName = settings('name') ?? '';
        $this->studyCenterPhone = settings('phone') ?? '';
    }

    public function loadTemplates()
    {
        $this->templates = SmsTemplate::where('status', 'registered')
            ->orderBy('name')
            ->get();
    }

    public function selectTemplate()
    {
        if ($this->selectedTemplateId) {
            $this->selectedTemplate = SmsTemplate::find($this->selectedTemplateId);
        } else {
            $this->selectedTemplate = null;
        }
    }

    public function updatedSelectedTemplateId()
    {
        $this->selectTemplate();
    }

    public function getSmsCount(): int
    {
        $count = 0;

        foreach ($this->receivers as $receiver) {
            if ($this->showStudents && isset($receiver['phone'])) {
                $count++;
            }

            if ($this->showParents && isset($receiver['parent_phone']) && $receiver['parent_phone']) {
                $count++;
            }
        }

        return $count;
    }

    public function getCharacterCount(): int
    {
        if (! $this->selectedTemplate) {
            return 0;
        }

        return mb_strlen($this->selectedTemplate->content);
    }

    /**
     * Telefon raqamidan operatorni aniqlash
     */
    private function detectOperator(string $phone): string
    {
        // Barcha noto'g'ri belgilarni olib tashlash
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // 998 prefiksini olib tashlash
        $phone = preg_replace('/^998/', '', $phone);

        // Agar 9 raqamdan kam bo'lsa, unknown qaytarish
        if (strlen($phone) < 9) {
            return 'unknown';
        }

        $prefix = substr($phone, 0, 2);

        $operators = [
            '97' => 'mobiuz',
            '88' => 'mobiuz',
            '87' => 'mobiuz',
            '93' => 'ucell',
            '94' => 'ucell',
            '50' => 'ucell',
            '90' => 'beeline',
            '91' => 'beeline',
            '99' => 'uzmobile',
            '77' => 'uzmobile',
            '70' => 'uzmobile',
            '95' => 'uzmobile',
            '33' => 'humans',
            '98' => 'perfectum',
            '20' => 'oq',
        ];

        return $operators[$prefix] ?? 'unknown';
    }

    /**
     * Operator va eskiz_status ga qarab SMS narxini qaytaradi
     */
    private function getGsm7ExtensionChars(): array
    {
        // GSM-7 extension table (each counts as 2 septets)
        return ['^', '{', '}', '\\', '[', ']', '~', '|', '€'];
    }

    /**
     * Returns true if the text can be encoded in GSM-7 (basic + extension).
     * If false => must be Unicode (UCS-2).
     */
    private function isGsm7(string $text): bool
    {
        // Normalize common "smart punctuation" that often sneaks in and forces Unicode
        // We do NOT auto-replace; only detect. Replacing should be a separate UX decision.
        $ext = $this->getGsm7ExtensionChars();

        // Check each unicode grapheme-ish (simple mb split)
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($chars)) {
            return false;
        }

        foreach ($chars as $ch) {
            // New lines are allowed in GSM-7
            if ($ch === "\n" || $ch === "\r") {
                continue;
            }

            // Extension chars are allowed
            if (in_array($ch, $ext, true)) {
                continue;
            }

            // Basic GSM-7 allowed set via regex.
            // This is a pragmatic whitelist. Anything outside forces Unicode.
            // Includes common ASCII punctuation and space.
            if (preg_match('/^[\x20-\x7E]$/', $ch) === 1) {
                // But some ASCII are not GSM-7 in some implementations; we accept typical GSM behavior.
                continue;
            }

            // If it's not ASCII basic and not extension, it's Unicode.
            return false;
        }

        return true;
    }

    /**
     * Compute message length in "units" used for SMS segmentation:
     * - GSM-7 => septet length, where extension chars count as 2.
     * - Unicode => UCS-2 char count (mb_strlen).
     */
    private function getSmsLengthUnits(string $text): array
    {
        $isGsm7 = $this->isGsm7($text);
        if (! $isGsm7) {
            return ['encoding' => 'unicode', 'length' => mb_strlen($text)];
        }

        $ext = $this->getGsm7ExtensionChars();
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $len = 0;

        foreach ($chars as $ch) {
            if ($ch === "\n" || $ch === "\r") {
                $len += 1;

                continue;
            }

            if (in_array($ch, $ext, true)) {
                $len += 2; // ESC + char
            } else {
                $len += 1;
            }
        }

        return ['encoding' => 'gsm7', 'length' => $len];
    }

    /**
     * Calculate SMS parts based on encoding and length
     */
    private function calculateSmsParts(int $length, string $encoding): int
    {
        if ($length <= 0) {
            return 0;
        }

        if ($encoding === 'unicode') {
            // UCS-2: 70 single, 67 concatenated
            if ($length <= 70) {
                return 1;
            }

            return (int) ceil($length / 67);
        }

        // GSM-7: 160 single, 153 concatenated
        if ($length <= 160) {
            return 1;
        }

        return (int) ceil($length / 153);
    }

    /**
     * Templateni keylar bilan almashtirish
     */
    private function replaceTemplateKeys(string $template, $receiver): string
    {
        $receiverName = isset($receiver['name']) && isset($receiver['surname'])
            ? $receiver['name'].' '.$receiver['surname']
            : '';

        return str_replace(
            ['{STUDY_CENTER}', '{STUDENT}', '{TEACHER}', '{PHONE}'],
            [
                $this->studyCenterName ?? '',
                $receiverName,
                '',
                $this->studyCenterPhone ? formattedPhone($this->studyCenterPhone) : '',
            ],
            $template
        );
    }

    /**
     * SMS qismlarini hisoblaydi (template average)
     */
    public function calculateSmsPartsCount(): int
    {
        if (! $this->selectedTemplate) {
            return 0;
        }

        $content = $this->selectedTemplate->content;
        $meta = $this->getSmsLengthUnits($content);
        $length = $meta['length'];
        $encoding = $meta['encoding'];

        return $this->calculateSmsParts($length, $encoding);
    }

    /**
     * Har bir qabul qiluvchi uchun SMS qismlarini hisoblash
     */
    private function calculateSmsForEachReceiver(): array
    {
        if (! $this->selectedTemplate) {
            return ['total_sms' => 0, 'min' => 0, 'max' => 0, 'avg' => 0];
        }

        $totalSms = 0;
        $smsCountsList = [];

        foreach ($this->receivers as $receiver) {
            // O'quvchiga SMS yuborish
            if ($this->showStudents && isset($receiver['phone'])) {
                $phone = unformattedPhone($receiver['phone']);
                $message = $this->replaceTemplateKeys($this->selectedTemplate->content, $receiver);
                $meta = $this->getSmsLengthUnits($message);
                $length = $meta['length'];
                $encoding = $meta['encoding'];
                $parts = $this->calculateSmsParts($length, $encoding);

                $totalSms += $parts;
                $smsCountsList[] = $parts;
            }

            // Ota-onaga SMS yuborish
            if ($this->showParents && isset($receiver['parent_phone']) && $receiver['parent_phone']) {
                $phone = unformattedPhone($receiver['parent_phone']);
                $message = $this->replaceTemplateKeys($this->selectedTemplate->content, $receiver);
                $meta = $this->getSmsLengthUnits($message);
                $length = $meta['length'];
                $encoding = $meta['encoding'];
                $parts = $this->calculateSmsParts($length, $encoding);

                $totalSms += $parts;
                $smsCountsList[] = $parts;
            }
        }

        return [
            'total_sms' => $totalSms,
            'min' => ! empty($smsCountsList) ? min($smsCountsList) : 0,
            'max' => ! empty($smsCountsList) ? max($smsCountsList) : 0,
            'avg' => ! empty($smsCountsList) ? round(array_sum($smsCountsList) / count($smsCountsList), 1) : 0,
        ];
    }

    /**
     * Jami yuborilishi kerak bo'lgan SMS qismlari soni
     */
    public function getTotalSmsPartsCount(): int
    {
        $stats = $this->calculateSmsForEachReceiver();

        return $stats['total_sms'];
    }

    /**
     * SMS statistikasini olish
     */
    public function getSmsStatistics(): array
    {
        return $this->calculateSmsForEachReceiver();
    }

    /**
     * SMS preview ma'lumotlarini yuborish (pagination bilan)
     */
    public function openPreview(): void
    {
        try {
            $allPreviews = $this->getSmsPreview();
            $totalCount = count($allPreviews);

            // Statistika hisoblash
            $this->previewStats = [
                'total_count' => $totalCount,
                'total_sms' => 0,
            ];

            foreach ($allPreviews as $preview) {
                $this->previewStats['total_sms'] += $preview['sms_parts'];
            }

            $this->previewData = $allPreviews;
            $this->currentPage = 1;
            $this->showPreviewModal = true;
        } catch (\Exception $e) {
            $this->previewData = [];
            $this->previewStats = [
                'total_count' => 0,
                'total_sms' => 0,
            ];
        }
    }

    public function changePage($page): void
    {
        $this->currentPage = $page;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewData = [];
        $this->previewStats = [];
        $this->currentPage = 1;
    }

    /**
     * SMS preview olish
     */
    public function getSmsPreview(): array
    {
        if (! $this->selectedTemplate) {
            return [];
        }

        $previews = [];

        foreach ($this->receivers as $receiver) {
            // O'quvchiga SMS
            if ($this->showStudents && isset($receiver['phone'])) {
                $phone = $receiver['phone'];
                $cleanPhone = unformattedPhone($phone);
                $message = $this->replaceTemplateKeys($this->selectedTemplate->content, $receiver);
                $meta = $this->getSmsLengthUnits($message);
                $length = $meta['length'];
                $encoding = $meta['encoding'];
                $parts = $this->calculateSmsParts($length, $encoding);
                $operator = $this->detectOperator($cleanPhone);

                $previews[] = [
                    'receiver_name' => ($receiver['name'] ?? '').' '.($receiver['surname'] ?? ''),
                    'phone' => formattedPhone($phone),
                    'message' => $message,
                    'character_count' => $length,
                    'encoding' => $encoding,
                    'is_cyrillic' => $encoding === 'unicode',
                    'sms_parts' => $parts,
                    'operator' => $operator,
                    'recipient_type' => 'student',
                ];
            }

            // Ota-onaga SMS
            if ($this->showParents && isset($receiver['parent_phone']) && $receiver['parent_phone']) {
                $phone = $receiver['parent_phone'];
                $cleanPhone = unformattedPhone($phone);
                $message = $this->replaceTemplateKeys($this->selectedTemplate->content, $receiver);
                $meta = $this->getSmsLengthUnits($message);
                $length = $meta['length'];
                $encoding = $meta['encoding'];
                $parts = $this->calculateSmsParts($length, $encoding);
                $operator = $this->detectOperator($cleanPhone);

                $previews[] = [
                    'receiver_name' => ($receiver['name'] ?? '').' '.($receiver['surname'] ?? '').' (Ota-ona)',
                    'phone' => formattedPhone($phone),
                    'message' => $message,
                    'character_count' => $length,
                    'encoding' => $encoding,
                    'is_cyrillic' => $encoding === 'unicode',
                    'sms_parts' => $parts,
                    'operator' => $operator,
                    'recipient_type' => 'parent',
                ];
            }
        }

        return $previews;
    }

    public function render()
    {
        $smsStats = $this->getSmsStatistics();

        $templateEncoding = 'gsm7';
        if ($this->selectedTemplate) {
            $templateMeta = $this->getSmsLengthUnits($this->selectedTemplate->content);
            $templateEncoding = $templateMeta['encoding'];
        }

        return view('livewire.sms.send-sms', [
            'receiverCount' => $this->getSmsCount(),
            'characterCount' => $this->getCharacterCount(),
            'totalSmsCount' => $smsStats['total_sms'],
            'minSmsPerMessage' => $smsStats['min'],
            'maxSmsPerMessage' => $smsStats['max'],
            'avgSmsPerMessage' => $smsStats['avg'],
            'encoding' => $templateEncoding,
            'isCyrillic' => $templateEncoding === 'unicode',
        ]);
    }
}
