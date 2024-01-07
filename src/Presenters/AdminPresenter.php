<?php

namespace Crm\AdminModule\Presenters;

use Crm\AdminModule\Components\AdminMenu\AdminMenuFactoryInterface;
use Crm\AdminModule\Events\AdminRequestInsecureEvent;
use Crm\AdminModule\Forms\ChangeLocaleFormFactory;
use Crm\AdminModule\Helpers\SecuredAdminAccess;
use Crm\ApplicationModule\Presenters\BasePresenter;
use Crm\UsersModule\Repository\UsersRepository;
use Nette\Application\Attributes\Persistent;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Attributes\Inject;

class AdminPresenter extends BasePresenter
{
    #[Persistent]
    public $state;

    #[Persistent]
    public $text;

    #[Persistent]
    public $payment_method;

    #[Inject]
    public SecuredAdminAccess $securedAdminAccess;

    protected $onPage = 50;

    public function startup()
    {
        parent::startup();

        $this->onlyLoggedIn();

        $userRepository = $this->container->getByType(UsersRepository::class);
        $user = $userRepository->find($this->getUser()->id);
        if (!$user) {
            $this->getUser()->logout(true);
            $this->redirect($this->applicationConfig->get('not_logged_in_route'), ['back' => $this->storeRequest()]);
        }

        if (UsersRepository::ROLE_ADMIN !== $user->role) {
            throw new ForbiddenRequestException();
        }

        // check user's access to presenter's signal
        if ($this->getSignal() !== null) {
            [$signalReceiver, $signal] = $this->getSignal();
            // non-empty signal receiver indicates submit action of form / component
            // for now we want to restrict only presenter's signals
            if ($signalReceiver === '') {
                // lower first letter of signal name; resources in DB are stored with first letter lowercased
                // (and getAction() returns lowercased name)
                $signal = lcfirst($signal);
                if (!$this->getUser()->isAllowed($this->getName(), $signal)) {
                    throw new ForbiddenRequestException();
                }
            }
        }

        // check user's access to presenter's action
        if (!$this->getUser()->isAllowed($this->getName(), $this->getAction())) {
            throw new ForbiddenRequestException();
        }

        // require secure login
        if (!$this->securedAdminAccess->isSecure($user)) {
            $this->emitter->emit(new AdminRequestInsecureEvent($this->storeRequest()));

            $this->flashMessage($this->translator->translate('admin.security.unsecure_login'), 'warning');
            $this->getUser()->logout(true);
            $this->redirect($this->applicationConfig->get('not_logged_in_route'), ['back' => $this->storeRequest()]);
        }

        $this->setLayout('admin');
        $this->template->current_user = $user;
        $this->template->admin_logo = $this->applicationConfig->get('admin_logo');
    }

    public function createComponentAdminMenu(AdminMenuFactoryInterface $factory)
    {
        $adminMenu = $factory->create();
        $adminMenu->setMenuItems($this->applicationManager->getAdminMenuItems());

        return $adminMenu;
    }

    public function createComponentChangeLocale(ChangeLocaleFormFactory $factory)
    {
        $form = $factory->create();
        $factory->onChange = function () {
            $this->redirect('this', ['locale' => null]);
        };
        return $form;
    }

    public function adminFilterSubmitted($form, $values)
    {
        $this->redirect($this->action, array_map(function ($item) {
            return $item ?: null;
        }, (array)$values));
    }
}
