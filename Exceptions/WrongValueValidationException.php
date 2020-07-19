<?php
namespace Exceptions;

use Exception;

class WrongValueValidationException extends ValidationException {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct("wrong value for {$message}", ExceptionCode::VALIDATION_WRONG_VALUE, $previous);
    }
}