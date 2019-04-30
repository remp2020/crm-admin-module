<?php

namespace Crm\AdminModule\Forms;

use Kdyby\Translation\LocaleResolver\SessionResolver;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;

class ChangeLocaleFormFactory
{
    private $translator;

    private $userLocaleResolver;

    public $onChange;

    public function __construct(Translator $translator, SessionResolver $userLocaleResolver)
    {
        $this->translator = $translator;
        $this->userLocaleResolver = $userLocaleResolver;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;

        $form->setRenderer(new BootstrapInlineRenderer());
        $form->addProtection();
        $form->setTranslator($this->translator);

        $locales = [];
        foreach ($this->translator->getAvailableLocales() as $l) {
            $locales[$l] = $l;
        }

        $locale = $form->addSelect('locale', null, $locales)
            ->setAttribute('onChange', 'submit()');
        $locale->getControlPrototype()->addAttributes([
            'class' => 'select2',
            'style' => 'width: 80px',
        ]);

        $form->setDefaults([
            'locale' => $this->translator->getLocale(),
        ]);

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $this->userLocaleResolver->setLocale($values->locale);
        $this->onChange->__invoke();
    }
}
