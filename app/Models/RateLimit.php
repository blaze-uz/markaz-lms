<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateLimit extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'limit_until'];

    /**
     * Check if a rate limit is active
     *
     * @param string $key Rate limit key
     * @return bool|int False if not limited, or seconds remaining if limited
     */
    public static function isLimited(string $key): bool|int
    {
        $limit = self::where('key', $key)
            ->where('limit_until', '>', time())
            ->first();

        if ($limit) {
            return $limit->limit_until - time(); // seconds remaining
        }

        return false;
    }

    /**
     * Set a rate limit
     *
     * @param string $key Rate limit key
     * @param int $seconds Duration in seconds
     * @return void
     */
    public static function setLimit(string $key, int $seconds): void
    {
        $limitUntil = time() + $seconds;

        self::updateOrCreate(
            ['key' => $key],
            ['limit_until' => $limitUntil]
        );
    }

    /**
     * Clear expired rate limits
     *
     * @return int Number of deleted records
     */
    public static function clearExpired(): int
    {
        return self::where('limit_until', '<', time())->delete();
    }
}
