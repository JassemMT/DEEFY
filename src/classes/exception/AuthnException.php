<?php
declare(strict_types=1);

namespace iutnc\deefy\exception;

class AuthnException extends \Exception {
    public const INVALID_CREDENTIALS = 1;
    public const USER_NOT_FOUND = 2;
}