<?php

namespace Crm\AdminModule\Components\AdminMenu;

use Crm\ApplicationModule\Models\Menu\MenuContainerInterface;
use Crm\ApplicationModule\Models\Menu\MenuItem;
use Crm\ApplicationModule\Models\Menu\MenuItemInterface;
use Nette\Application\UI\Control;
use Nette\Security\Authorizator;
use Nette\Security\User;

/**
 * Component used for rendering admin menu.
 *
 * Fetches items from MenuContainerInterface
 * transforms it to multidimensional array
 * and renders to bootstrap styled latte template.
 *
 * @package Crm\AdminModule\Components
 */
class AdminMenu extends Control
{
    private $templateName = 'admin_menu.latte';

    /** @var MenuContainerInterface */
    private $menuItems;

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function setMenuItems(MenuContainerInterface $menuItems)
    {
        $this->menuItems = $menuItems;
    }

    private function format($link)
    {
        $parts = explode(':', $link);
        $module = $parts[1] . ':' . $parts[2];
        $action = 'default';
        if (!empty($parts[3])) {
            $action = lcfirst($parts[3]);
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
                    if ($subItem->internal()) {
                        list($module, $action) = $this->format($subItem->link());
                    } else {
                        $module = Authorizator::ALLOW;
                        $action = Authorizator::ALLOW;
                    }

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
                if ($menuItem->internal()) {
                    list($module, $action) = $this->format($menuItem->link());
                } else {
                    $module = Authorizator::ALLOW;
                    $action = Authorizator::ALLOW;
                }
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
