<?php
namespace Exceptions;

use Exception;

class FormatHttpTransactionException extends HttpTransactionException {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct("unexpected format: {$message}", ExceptionCode::HTTP_TRANSACTION_FORMAT, $previous);
    }
}