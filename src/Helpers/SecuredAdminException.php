<?php

namespace Crm\AdminModule\Helpers;

use Exception;

class SecuredAdminException extends Exception
{
    private string $stringCode;

    public function __construct(string $stringCode)
    {
        parent::__construct("Secure login of admin account failed. Error: [{$stringCode}].");
        $this->stringCode = $stringCode;
    }

    public function getStringCode(): string
    {
        return $this->stringCode;
    }
}
