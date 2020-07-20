<?php
namespace Exceptions;

use Exception;

class MissingParameterApiException extends ApiException {
    public function __construct($parameterName, $code = 0, Exception $previous = null) {
        parent::__construct("missing parameter: {$parameterName}", ExceptionCode::API_MISSING_PARAMETER, $previous);
    }
}