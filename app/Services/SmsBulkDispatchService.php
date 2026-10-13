<?php

namespace App\Services;

use App\Jobs\BulkSendSms;

class SmsBulkDispatchService
{
    public const DEFAULT_CHUNK_SIZE = 50;

    /**
     * @param  array<int>  $smsIds
     */
    public static function dispatchFromSmsIds(array $smsIds, int $chunkSize = self::DEFAULT_CHUNK_SIZE): void
    {
        $smsIds = array_values(array_unique(array_filter($smsIds, fn ($id) => is_numeric($id))));

        foreach (array_chunk($smsIds, max(1, $chunkSize)) as $chunk) {
            BulkSendSms::dispatch(array_map('intval', $chunk));
        }
    }
}
