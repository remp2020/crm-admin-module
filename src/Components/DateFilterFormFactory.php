<?php

namespace Crm\AdminModule\Components;

use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;

class DateFilterFormFactory
{
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function create($dateFrom, $dateTo)
    {
        $form = new Form;
        $form->setRenderer(new BootstrapInlineRenderer());
        $form->addText('date_from', $this->translator->translate('admin.components.date_filter_form.date_from'))
            ->setAttribute('autofocus')
            ->setAttribute('class', 'flatpickr');
        $form->addText('date_to', $this->translator->translate('admin.components.date_filter_form.date_to'))
            ->setAttribute('class', 'flatpickr');

        $form->addSubmit('send', $this->translator->translate('admin.components.date_filter_form.submit'))
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-filter"></i> ' . $this->translator->translate('admin.components.date_filter_form.submit'));

        $form->setDefaults([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
        return $form;
    }
}
