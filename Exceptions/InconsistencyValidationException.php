<?php
namespace Exceptions;

use Exception;

class InconsistencyValidationException extends ValidationException {
    public function __construct($message, Exception $previous = null) {
        parent::__construct("inconsistency detected: {$message}", ExceptionCode::VALIDATION_INCONSISTENCY, $previous);
    }
}