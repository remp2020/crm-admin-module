<?php

namespace Crm\AdminModule\Presenters;

use Crm\ApplicationModule\Components\VisualPaginator;
use Crm\ApplicationModule\Repository\AuditLogRepository;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;

class AuditLogAdminPresenter extends AdminPresenter
{
    private $auditLogRepository;

    public function __construct(AuditLogRepository $auditLogRepository)
    {
        $this->auditLogRepository = $auditLogRepository;
        parent::__construct();
    }

    public function renderDefault()
    {
        $records = $this->getFilteredLogs();
        $count = $records->count('*');
        $this->template->count = $count;

        $vp = new VisualPaginator();
        $this->addComponent($vp, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->setItemCount($count);
        $paginator->setItemsPerPage($this->onPage);

        $this->template->vp = $vp;
        $this->template->records = $records->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->createdAtFrom = $this->request->getParameter('created_at_from');
        $this->template->createdAtTo = $this->request->getParameter('created_at_to');
    }

    public function createComponentFilterForm()
    {
        $form = new Form;
        $form->setRenderer(new BootstrapInlineRenderer());

        $form->addSelect('operation', '', [
            AuditLogRepository::OPERATION_CREATE => 'Create',
            AuditLogRepository::OPERATION_UPDATE => 'Update',
            AuditLogRepository::OPERATION_DELETE => 'Delete',
        ])
            ->setPrompt($this->translate('default.operation'))
            ;

        $form->addText('table', $this->translate('default.table'))
            ->setAttribute('placeholder', $this->translator->translate('application.common.eg') .' users');

        $form->addText('signature', $this->translate('default.signature'))
            ->setAttribute('placeholder', $this->translator->translate('application.common.eg') .' 12345');

        $form->addText('created_at_from', $this->translate('default.created_at_from'))
            ->setAttribute('placeholder', $this->translator->translate('application.common.eg').' 2016-02-29');

        $form->addText('created_at_to', $this->translate('default.created_at_to'))
            ->setAttribute('placeholder', $this->translator->translate('application.common.eg').' 2020-02-29');

        $form->addSubmit('send', $this->translate('default.filter'))
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-filter"></i> Filter');

        $presenter = $this;
        $form->addSubmit('cancel', $this->translate('default.cancel_filter'))
            ->onClick[] = function () use ($presenter) {
                $presenter->redirect('AuditLog:Default', ['text' => '']);
            };

        $form->onSuccess[] = [$this, 'filterSubmitted'];
        $form->setDefaults([
            'table' => $this->request->getParameter('table'),
            'signature' => $this->request->getParameter('signature'),
            'operation' => $this->request->getParameter('operation'),
            'created_at_from' => $this->request->getParameter('created_at_from'),
            'created_at_to' => $this->request->getParameter('created_at_to'),
        ]);
        return $form;
    }

    public function filterSubmitted($form, $values)
    {
        $this->redirect('Default', array_filter([
            'table' => $values['table'],
            'signature' => $values['signature'],
            'operation' => $values['operation'],
            'created_at_from' => $values['created_at_from'],
            'created_at_to' => $values['created_at_to'],
        ]));
    }

    private function getFilteredLogs()
    {
        $auditRecords = $this->auditLogRepository->getTable();
        if (($value = $this->request->getParameter('table')) != null) {
            $auditRecords->where('table_name', $value);
        }
        if (($value = $this->request->getParameter('signature')) != null) {
            $auditRecords->where('signature', $value);
        }
        if (($value = $this->request->getParameter('operation')) != null) {
            $auditRecords->where('operation', $value);
        }
        if (($value = $this->request->getParameter('created_at_from')) != null) {
            $auditRecords->where('created_at > ?', $value);
        }
        if (($value = $this->request->getParameter('created_at_to')) != null) {
            $auditRecords->where('created_at < ?', $value);
        }
        $auditRecords->order('created_at DESC');
        return $auditRecords;
    }

    private function translate($key)
    {
        return $this->translator->translate('application.admin.audit_log.' . $key);
    }
}
