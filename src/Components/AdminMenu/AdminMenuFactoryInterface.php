<?php

namespace Crm\AdminModule\Components;

interface AdminMenuFactoryInterface
{
    /** @return AdminMenu */
    public function create();
}
