<?php

namespace Crm\AdminModule\Components;

use Crm\ApplicationModule\UI\Form;
use DateInterval;
use DateTime;
use Nette\Localization\Translator;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;

/**
 * Basic month year datepicker form filter component.
 *
 * Returns Nette\Application\UI\Form with from/to date inputs
 * + optional form container for adding custom fields.
 *
 * @package Crm\AdminModule\Components
 */
class YearMonthFilterFormFactory
{
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function create(DateTime $minDateFrom, DateTime $maxDateTo, $filterDateFrom, $filterDateTo)
    {
        $form = new Form;
        $form->setRenderer(new BootstrapInlineRenderer());
        $form->setTranslator($this->translator);

        $selectOptions = $this->prepareDateSelectRange($minDateFrom, $maxDateTo);

        $selectFrom = $form->addSelect(
            'date_from',
            'admin.components.date_filter_form.date_from',
            $selectOptions,
        )->setPrompt('--');
        $selectFrom->getControlPrototype()->addAttributes(['class' => 'select2']);

        $selectTo = $form->addSelect(
            'date_to',
            'admin.components.date_filter_form.date_to',
            $selectOptions,
        )->setPrompt('--');
        $selectTo->getControlPrototype()->addAttributes(['class' => 'select2']);


        $form->addSubmit('send', $this->translator->translate('admin.components.date_filter_form.submit'))
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-filter"></i> ' . $this->translator->translate('admin.components.date_filter_form.submit'));

        $form->setDefaults([
            'date_from' => $filterDateFrom,
            'date_to' => $filterDateTo,
        ]);
        return $form;
    }

    public function formatDateTimeToValue(DateTime $dateTime)
    {
        return $dateTime->format('Y-n');
    }

    private function prepareDateSelectRange(DateTime $minDateFrom, DateTime $maxDateTo)
    {
        $result = [];
        $current = clone $minDateFrom;
        $oneMonth = new DateInterval('P1M');
        while ($current <= $maxDateTo) {
            $result[$this->formatDateTimeToValue($current)] = $current->format('F') . ' ' . $current->format('Y');
            $current->add($oneMonth);
        }

        return $result;
    }
}
