<?php

namespace Exceptions;

use Exception;

class HttpTransactionException extends Exception
{
    public function __construct($message, $code, Exception $previous = null)
    {
        parent::__construct("HTTP transaction exception, {$message}", $code, $previous);
    }
}