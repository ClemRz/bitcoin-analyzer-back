<?php

namespace Exceptions;

use Exception;

class UnsupportedValueApiException extends ApiException
{
    public function __construct($parameterName, Exception $previous = null)
    {
        parent::__construct("unsupported value for parameter: {$parameterName}", ExceptionCode::API_UNSUPPORTED_VALUE, $previous);
    }

}