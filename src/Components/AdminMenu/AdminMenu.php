<?php

namespace Crm\AdminModule\Components;

use Crm\ApplicationModule\Menu\MenuContainerInterface;
use Crm\ApplicationModule\Menu\MenuItem;
use Crm\ApplicationModule\Menu\MenuItemInterface;
use Nette\Application\UI;
use Nette\Security\IAuthorizator;
use Nette\Security\User;

class AdminMenu extends UI\Control
{
    private $templateName = 'admin_menu.latte';

    /** @var  MenuContainerInterface */
    private $menuItems;

    private $user;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function setMenuItems(MenuContainerInterface $menuItems)
    {
        $this->menuItems = $menuItems;
    }

    private function format($link)
    {
        if (strpos($link, 'http') === 0) { // external link
            return [IAuthorizator::ALLOW, IAuthorizator::ALLOW];
        }
        $parts = explode(':', $link);
        $module = $parts[1] . ':' . $parts[2];
        $action = 'default';
        if (!isset($parts[3])) {
            $action = $parts[3];
        }
        return [$module, $action];
    }

    private function copyItem(MenuItemInterface $menuItem)
    {
        return new MenuItem(
            $menuItem->name(),
            $menuItem->link(),
            $menuItem->icon(),
            $menuItem->position(),
            $menuItem->internal()
        );
    }

    private function getMenuItems()
    {
        $menuItems = $this->menuItems->getMenuItems();
        $result = [];
        foreach ($menuItems as $menuItem) {
            if ($menuItem->hasSubItems()) {
                $subItems = [];
                foreach ($menuItem->subItems() as $subItem) {
                    if (!$subItem->link()) {
                        continue;
                    }
                    list($module, $action) = $this->format($subItem->link());
                    if ($this->user->isAllowed($module, $action)) {
                        $subItems[] = $subItem;
                    }
                }
                if (count($subItems) > 0) {
                    $newItem = $this->copyItem($menuItem);
                    foreach ($subItems as $subItem) {
                        $newItem->addChild($subItem);
                    }
                    $result[] = $newItem;
                }
            } else {
                list($module, $action) = $this->format($menuItem->link());
                if ($this->user->isAllowed($module, $action)) {
                    $result[] = $menuItem;
                }
            }
        }
        return $result;
    }

    public function render()
    {
        $this->template->menuItems = $this->getMenuItems();
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
