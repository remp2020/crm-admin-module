<?php

namespace Crm\AdminModule\Events;

use League\Event\AbstractEvent;

class AdminRequestInsecureEvent extends AbstractEvent
{
    private $back;

    public function __construct(string $back)
    {
        $this->back = $back;
    }

    public function getBack(): string
    {
        return $this->back;
    }
}
