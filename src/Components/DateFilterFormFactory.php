<?php

namespace Crm\AdminModule\Components;

use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;

/**
 * Basic date form filter component.
 *
 * Returns Nette\Application\UI\Form with from/to date inputs
 * + optional form container for adding custom fields.
 *
 * @package Crm\AdminModule\Components
 */
class DateFilterFormFactory
{
    const OPTIONAL = 'optional';

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
            ->setHtmlAttribute('autofocus')
            ->setHtmlAttribute('class', 'flatpickr');
        $form->addText('date_to', $this->translator->translate('admin.components.date_filter_form.date_to'))
            ->setHtmlAttribute('class', 'flatpickr');

        $form->addContainer(self::OPTIONAL);

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
