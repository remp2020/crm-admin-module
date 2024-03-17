<?php

namespace Crm\AdminModule\Helpers;

use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use Crm\UsersModule\Repositories\UserMetaRepository;
use Crm\UsersModule\Repositories\UsersRepository;
use Nette\Database\Table\ActiveRow;
use Nette\Http\Session;

class SecuredAdminAccess
{
    // Add this user meta flag only if user has a Google SSO account paired.
    public const USER_META_SECURE_LOGIN_ALLOWED = 'secure_login_allowed';
    public const ERROR_CODE_INSECURE_SESSION = 'insecure_session';
    public const ERROR_CODE_MISSING_USER_SECURE_FLAG = 'missing_user_secure_flag';

    public function __construct(
        private Session $session,
        private ApplicationConfig $applicationConfig,
        private UserMetaRepository $userMetaRepository
    ) {
    }

    public function canLinkOrUnlinkAccount(ActiveRow $user): bool
    {
        if ($user->role !== UsersRepository::ROLE_ADMIN) {
            return true;
        }

        if (!$this->applicationConfig->get('admin_secure_login_check')) {
            return true;
        }

        if (!$this->userMetaRepository->exists($user, self::USER_META_SECURE_LOGIN_ALLOWED)) {
            return true;
        }

        // otherwise, do not enable linking/unlinking, since someone can bypass SSO authentication just by adding
        // additional linked account and log in using that
        return false;
    }

    public function setSecure(bool $value)
    {
        $section = $this->session->getSection('admin');
        $section->secure_login = $value;
    }

    /**
     * @throws SecuredAdminException
     */
    public function isSecure(ActiveRow $user, bool $throwExceptionIfNotSecure = false): bool
    {
        if ($this->applicationConfig->get('admin_secure_login_check')) {
            $section = $this->session->getSection('admin');

            if ($section->secure_login !== true) {
                if ($throwExceptionIfNotSecure) {
                    throw new SecuredAdminException(self::ERROR_CODE_INSECURE_SESSION);
                }

                return false;
            }

            if (!$this->userMetaRepository->exists($user, self::USER_META_SECURE_LOGIN_ALLOWED)) {
                if ($throwExceptionIfNotSecure) {
                    throw new SecuredAdminException(self::ERROR_CODE_MISSING_USER_SECURE_FLAG);
                }

                return false;
            }
        }
        return true;
    }
}
