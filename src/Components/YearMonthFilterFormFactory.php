<?php

namespace Crm\AdminModule\Components;

use Crm\InternalModule\Repositories\PaymentItemMonthlyRevenuesRepository;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\DateTime;
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

    private $paymentItemMonthlyRevenuesRepository;

    public function __construct(
        PaymentItemMonthlyRevenuesRepository $paymentItemMonthlyRevenuesRepository,
        ITranslator $translator
    ) {
        $this->translator = $translator;
        $this->paymentItemMonthlyRevenuesRepository = $paymentItemMonthlyRevenuesRepository;
    }

    public function create($dateFrom, $dateTo)
    {
        $form = new Form;
        $form->setRenderer(new BootstrapInlineRenderer());
        $form->setTranslator($this->translator);

        $selectDateRange = $this->paymentItemMonthlyRevenuesRepository->getDataDateRange();
        $selectFrom = $form->addSelect(
            'date_from',
            'admin.components.date_filter_form.date_from',
            $this->prepareDateSelectRange($selectDateRange)
        )->setPrompt('--');
        $selectFrom->getControlPrototype()->addAttributes(['class' => 'select2']);

        $selectTo = $form->addSelect(
            'date_to',
            'admin.components.date_filter_form.date_to',
            $this->prepareDateSelectRange($selectDateRange)
        )->setPrompt('--');
        $selectTo->getControlPrototype()->addAttributes(['class' => 'select2']);


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

    private function prepareDateSelectRange($selectDateRange)
    {
        $result = [];
        foreach ($selectDateRange as $item) {
            $result[$item->year . '-' . $item->month] = DateTime::createFromFormat('!m', $item->month)->format('F') . ' ' . $item->year;
        }

        return $result;
    }
}
