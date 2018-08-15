<?php

namespace Crm\AdminModule\Presenters;

use Crm\ApplicationModule\Components\AdminMenuFactoryInterface;
use Crm\ApplicationModule\Components\DetailWidgetFactoryInterface;
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

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Users:Sign:In');
        }

        $userRepository = $this->context->getByType('Crm\UsersModule\Repository\UsersRepository');
        $user = $userRepository->find($this->getUser()->id);
        if (!$user) {
            $this->redirect(':Users:Sign:In');
        }

        if (UsersRepository::ROLE_ADMIN !== $user->role) {
            $this->redirect(':Users:Sign:In');
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

    protected function createComponentDetailWidget(DetailWidgetFactoryInterface $factory)
    {
        $control = $factory->create();
        return $control;
    }
}
