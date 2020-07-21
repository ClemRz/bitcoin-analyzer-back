<?php

namespace Exceptions;

use Exception;

class ThirdPartyHttpTransactionException extends HttpTransactionException
{
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct("third party error: {$message}", ExceptionCode::HTTP_TRANSACTION_THIRD_PARTY, $previous);
    }
}