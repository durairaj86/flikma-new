<?php

namespace App\Exceptions;

use Exception;

/**
 * Raised when a company has no AI token budget left, or not enough left to be
 * worth starting the request.
 *
 * Separate from a generic API failure on purpose: the user can't fix this by
 * retrying, they need to buy tokens, so the controller returns a distinct
 * status/message rather than a generic "scan failed".
 */
class AiTokenLimitExceededException extends Exception
{
    public function __construct(string $message = 'Your AI token limit has been reached. Please contact your admin to buy more tokens.')
    {
        parent::__construct($message);
    }
}
