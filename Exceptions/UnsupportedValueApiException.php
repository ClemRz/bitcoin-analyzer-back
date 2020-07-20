<?php

namespace Exceptions;

class UnsupportedValueApiException extends ApiException
{
    public function __construct($parameterName, $code = 0, Exception $previous = null)
    {
        parent::__construct("unsupported value for parameter: {$parameterName}", ExceptionCode::API_UNSUPPORTED_VALUE, $previous);
    }

}