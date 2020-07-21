<?php

namespace Exceptions;

use Exception;

class MissingParameterApiException extends ApiException
{
    public function __construct($parameterName, Exception $previous = null)
    {
        parent::__construct("missing parameter: {$parameterName}", ExceptionCode::API_MISSING_PARAMETER, $previous);
    }
}