<?php

namespace Crm\AdminModule\Forms;

use Contributte\Translation\Translator;
use Crm\ApplicationModule\Repositories\SnippetsRepository;
use Crm\ApplicationModule\UI\Form;
use Nette\Database\Table\ActiveRow;
use Tomaj\Form\Renderer\BootstrapRenderer;

class SnippetFormFactory
{
    /** @var SnippetsRepository  */
    private $snippetsRepository;

    /** @var Translator */
    private $translator;

    /* callback function */
    public $onUpdate;

    /* callback function */
    public $onCreate;

    private $snippet;

    public function __construct(
        SnippetsRepository $snippetsRepository,
        Translator $translator,
    ) {
        $this->snippetsRepository = $snippetsRepository;
        $this->translator = $translator;
    }

    /**
     * @return Form
     */
    public function create(ActiveRow $snippet = null)
    {
        $this->snippet = $snippet;
        $form = new Form;

        $form->setTranslator($this->translator);
        $form->setRenderer(new BootstrapRenderer());
        $form->addProtection();

        $form->addText('title', 'admin.data.snippets.fields.title')
            ->setRequired('admin.data.snippets.required.title')
            ->setHtmlAttribute('placeholder', 'admin.data.snippets.placeholder.title');

        $form->addText('identifier', 'admin.data.snippets.fields.identifier')
            ->setRequired('admin.data.snippets.required.identifier')
            ->setHtmlAttribute('placeholder', 'admin.data.snippets.placeholder.identifier');

        $form->addCheckbox('is_active', 'admin.data.snippets.fields.is_active');

        $form->addInteger('sorting', 'admin.data.snippets.fields.sorting')
            ->setRequired('admin.data.snippets.required.sorting')
            ->setDefaultValue(100)
            ->addRule($form::MIN, 'admin.data.snippets.validation.sorting_positive', 0)
            ->setHtmlAttribute('placeholder', 'admin.data.snippets.placeholder.sorting');

        $form->addTextArea('html', 'admin.data.snippets.fields.html')
            ->setHtmlAttribute('placeholder', 'admin.data.snippets.placeholder.html')
            ->setHtmlAttribute('rows', 30)
            ->setHtmlAttribute('data-codeeditor', ['name' => 'twig', 'base' => 'text/html']);

        $form->addSubmit('send', 'system.save');

        if ($snippet) {
            $form->setDefaults($snippet->toArray());
        }

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        if ($this->snippet) {
            if ($this->snippet->has_default_value && $values['html'] === $this->snippet->html) {
                // keep default value flag if it was there and html didn't change
                $values['has_default_value'] = true;
            }
            $this->snippetsRepository->update($this->snippet, $values);
            $this->onUpdate->__invoke($this->snippet);
        } else {
            $snippet = $this->snippetsRepository->add(
                $values->identifier,
                $values->title,
                $values->html,
                $values->sorting,
                $values->is_active,
            );
            $this->onCreate->__invoke($snippet);
        }
    }
}
