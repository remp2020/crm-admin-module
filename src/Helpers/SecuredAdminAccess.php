<?php

namespace Crm\AdminModule\Helpers;

use Crm\ApplicationModule\Config\ApplicationConfig;
use Nette\Http\Session;

class SecuredAdminAccess
{
    private $session;

    private $applicationConfig;

    public function __construct(
        Session $session,
        ApplicationConfig $applicationConfig
    ) {
        $this->session = $session;
        $this->applicationConfig = $applicationConfig;
    }

    public function setSecure(bool $value)
    {
        $section = $this->session->getSection('admin');
        $section->secure_login = $value;
    }

    public function isSecure(): bool
    {
        $section = $this->session->getSection('admin');
        if ($this->applicationConfig->get('admin_secure_login_check') && $section->secure_login !== true) {
            return false;
        }

        return true;
    }
}
