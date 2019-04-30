<?php

namespace Crm\AdminModule\Presenters;

use Crm\AdminModule\Components\AdminMenuFactoryInterface;
use Crm\AdminModule\Forms\ChangeLocaleFormFactory;
use Crm\ApplicationModule\Presenters\BasePresenter;
use Crm\UsersModule\Repository\UsersRepository;
use Nette\Application\ForbiddenRequestException;

class AdminPresenter extends BasePresenter
{
    /** @persistent */
    public $state;

    /** @persistent */
    public $text;

    /** @persistent */
    public $payment_method;

    protected $onPage = 50;

    public function startup()
    {
        parent::startup();

        $this->onlyLoggedIn();

        $userRepository = $this->context->getByType('Crm\UsersModule\Repository\UsersRepository');
        $user = $userRepository->find($this->getUser()->id);
        if (!$user) {
            $this->getUser()->logout(true);
            $this->redirect($this->applicationConfig->get('not_logged_in_route'), ['back' => $this->storeRequest()]);
        }

        if (UsersRepository::ROLE_ADMIN !== $user->role) {
            throw new ForbiddenRequestException();
        }

        if (!$this->getUser()->isAllowed($this->getName(), $this->getAction())) {
            throw new ForbiddenRequestException();
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
            $this->redirect('this');
        };
        return $form;
    }
}
