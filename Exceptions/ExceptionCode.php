<?php
namespace Exceptions;

abstract class ExceptionCode {
    public const API_MISSING_PARAMETER = 11;
    public const API_UNSUPPORTED_VALUE = 12;
    public const VALIDATION_WRONG_VALUE = 21;
    public const VALIDATION_INCONSISTENCY = 22;
    public const HTTP_TRANSACTION_FORMAT = 31;
    public const HTTP_TRANSACTION_THIRD_PARTY = 32;
}