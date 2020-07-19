<?php
namespace Exceptions;

use Exception;

class MissingParameterApiException extends ApiException {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct("missing parameter: {$message}", ExceptionCode::API_MISSING_PARAMETER, $previous);
    }
}