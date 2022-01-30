<?php

namespace Crm\AdminModule\Presenters;

use Crm\ApplicationModule\Components\VisualPaginator;
use Crm\ApplicationModule\Repository\AuditLogRepository;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;

class AuditLogAdminPresenter extends AdminPresenter
{
    /** @persistent */
    public $created_at_from;

    /** @persistent */
    public $created_at_to;

    /** @persistent */
    public $operation;

    /** @persistent */
    public $signature;

    /** @persistent */
    public $table;

    private $auditLogRepository;

    public function __construct(AuditLogRepository $auditLogRepository)
    {
        $this->auditLogRepository = $auditLogRepository;
        parent::__construct();
    }

    /**
     * @admin-access-level read
     */
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
            AuditLogRepository::OPERATION_CREATE => $this->translate('default.operation.create'),
            AuditLogRepository::OPERATION_UPDATE => $this->translate('default.operation.update'),
            AuditLogRepository::OPERATION_DELETE => $this->translate('default.operation.delete'),
        ])
            ->setPrompt($this->translate('default.operation.title'))
            ;

        $form->addText('table', $this->translate('default.table'))
            ->setHtmlAttribute('placeholder', $this->translate('default.eg') .' users');

        $form->addText('signature', $this->translate('default.signature'))
            ->setHtmlAttribute('placeholder', $this->translate('default.eg') .' 12345');

        $form->addText('created_at_from', $this->translate('default.created_at_from'))
            ->setHtmlAttribute('placeholder', $this->translate('default.eg').' 2016-02-29');

        $form->addText('created_at_to', $this->translate('default.created_at_to'))
            ->setHtmlAttribute('placeholder', $this->translate('default.eg').' 2020-02-29');

        $form->addSubmit('send', $this->translate('default.filter'))
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-filter"></i> ' . $this->translate('default.filter'));

        $presenter = $this;
        $form->addSubmit('cancel', $this->translate('default.cancel_filter'))
            ->onClick[] = function () use ($presenter) {
                $presenter->redirect('AuditLogAdmin:Default', ['text' => '']);
            };

        $form->onSuccess[] = [$this, 'adminFilterSubmitted'];
        $form->setDefaults([
            'table' => $this->request->getParameter('table'),
            'signature' => $this->request->getParameter('signature'),
            'operation' => $this->request->getParameter('operation'),
            'created_at_from' => $this->request->getParameter('created_at_from'),
            'created_at_to' => $this->request->getParameter('created_at_to'),
        ]);
        return $form;
    }

    private function getFilteredLogs()
    {
        $auditRecords = $this->auditLogRepository->getTable();
        if (($value = $this->request->getParameter('table')) !== null) {
            $auditRecords->where('table_name', $value);
        }
        if (($value = $this->request->getParameter('signature')) !== null) {
            $auditRecords->where('signature', $value);
        }
        if (($value = $this->request->getParameter('operation')) !== null) {
            $auditRecords->where('operation', $value);
        }
        if (($value = $this->request->getParameter('created_at_from')) !== null) {
            $auditRecords->where('created_at > ?', $value);
        }
        if (($value = $this->request->getParameter('created_at_to')) !== null) {
            $auditRecords->where('created_at < ?', $value);
        }
        $auditRecords->order('created_at DESC, id DESC');
        return $auditRecords;
    }

    private function translate($key)
    {
        return $this->translator->translate('admin.admin.audit_log.' . $key);
    }
}
