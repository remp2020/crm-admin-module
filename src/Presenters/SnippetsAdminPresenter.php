<?php

namespace Crm\AdminModule\Presenters;

use Crm\AdminModule\Forms\SnippetFormFactory;
use Crm\ApplicationModule\Snippet\Repository\SnippetsRepository;
use Nette\Database\Table\ActiveRow;

class SnippetsAdminPresenter extends AdminPresenter
{
    /** @var SnippetFormFactory */
    private $snippetFormFactory;

    /** @var SnippetsRepository */
    private $snippetsRepository;

    /** @var  @persistent */
    public $snippet;

    public function __construct(SnippetsRepository $snippetsRepository, SnippetFormFactory $snippetFormFactory)
    {
        parent::__construct();
        $this->snippetsRepository = $snippetsRepository;
        $this->snippetFormFactory = $snippetFormFactory;
    }

    /**
     * @admin-access-level read
     */
    public function renderDefault()
    {
        $this->template->snippets = $this->snippetsRepository->loadAll();
    }

    /**
     * @admin-access-level write
     */
    public function renderNew()
    {
    }

    /**
     * @admin-access-level read
     */
    public function renderShow($id)
    {
        $snippet = $this->snippetsRepository->find($id);
        $this->template->snippet = $snippet;
    }

    /**
     * @admin-access-level write
     */
    public function renderEdit($id)
    {
        $this->template->snippet = $this->snippetsRepository->find($id);
    }

    /**
     * @admin-access-level write
     */
    public function handleDelete($id)
    {
        $snippet = $this->snippetsRepository->find($id);
        $this->snippetsRepository->delete($snippet);
        $this->flashMessage($this->translator->translate('admin.admin.snippets.messages.snippet_deleted'));
        $this->redirect('default');
    }

    protected function createComponentSnippetForm()
    {
        $snippet = null;
        if (isset($this->params['id'])) {
            $snippet = $this->snippetsRepository->find($this->params['id']);
        }
        $form = $this->snippetFormFactory->create($snippet);
        $this->snippetFormFactory->onCreate = function (ActiveRow $snippet) {
            $this->flashMessage($this->translator->translate('admin.admin.snippets.messages.snippet_created'));
            $this->redirect('show', $snippet->id);
        };
        $this->snippetFormFactory->onUpdate = function (ActiveRow $snippet) {
            $this->flashMessage($this->translator->translate('admin.admin.snippets.messages.snippet_updated'));
            $this->redirect('show', $snippet->id);
        };
        return $form;
    }
}
