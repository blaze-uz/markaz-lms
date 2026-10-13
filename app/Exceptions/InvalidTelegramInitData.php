<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Telegram Mini App initData that is missing, unsigned, forged or too old.
 */
class InvalidTelegramInitData extends RuntimeException
{
}
