<?php

namespace Crm\AdminModule\Components;

use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;

class DateFilterFormFactory
{
    public function create($dateFrom, $dateTo)
    {
        $form = new Form;
        $form->setRenderer(new BootstrapInlineRenderer());
        $form->addText('date_from', 'Filter od')
            ->setAttribute('autofocus')
            ->setAttribute('class', 'flatpickr');
        $form->addText('date_to', 'Filter do')
            ->setAttribute('class', 'flatpickr');

        $form->addSubmit('send', 'Filter')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-filter"></i> Filter');

        $form->setDefaults([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
        return $form;
    }
}
