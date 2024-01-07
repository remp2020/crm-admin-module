<?php

namespace Crm\AdminModule\Components\AdminMenu;

interface AdminMenuFactoryInterface
{
    public function create(): AdminMenu;
}
