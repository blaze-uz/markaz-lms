<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EskizCallbackRequest;
use App\Models\Sms;
use Illuminate\Http\JsonResponse;

class EskizCallbackController extends Controller
{
    public function __invoke(EskizCallbackRequest $request): JsonResponse
    {
        $data = $request->validated();
        $sms = isset($data['user_sms_id']) ? Sms::find($data['user_sms_id']) : null;

        if (! $sms) {
            $externalIds = array_map('strval', array_filter([$data['message_id'] ?? null, $data['request_id'] ?? null]));
            $sms = $externalIds ? Sms::query()->whereIn('external_id', $externalIds)->first() : null;
        }

        $status = strtoupper($data['status']);
        $finalStatus = match ($status) {
            'DELIVERED', 'DELIVRD' => 'sent',
            'REJECTED', 'FAILED', 'UNDELIV', 'EXPIRED' => 'failed',
            default => null,
        };

        if ($sms && $finalStatus) {
            $attributes = [
                'status' => $finalStatus,
                'eskiz_status' => $status,
                'external_id' => isset($data['message_id']) || isset($data['request_id']) ? (string) ($data['message_id'] ?? $data['request_id']) : $sms->external_id,
                'error_log' => $finalStatus === 'failed' ? $status : null,
                'result_date' => $data['status_date'] ?? now(),
            ];

            if ($finalStatus === 'sent') {
                $attributes['sent_at'] = $data['status_date'] ?? now();
            }

            if (isset($data['price'])) {
                $attributes['price'] = $data['price'];
                $attributes['total_price'] = $data['total_price'] ?? $data['price'] * ($data['parts_count'] ?? 1);
            }

            if (isset($data['parts_count'])) {
                $attributes['parts_count'] = $data['parts_count'];
            }

            $sms->update($attributes);
        }

        return response()->json(['success' => true]);
    }
}
