<?php

namespace Crm\AdminModule\Forms;

use Crm\UsersModule\Repository\UsersRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tomaj\Form\Renderer\BootstrapInlineRenderer;

class ChangeLocaleFormFactory
{
    private $translator;

    private $usersRepository;

    private $user;

    public $onChange;

    public function __construct(Translator $translator, User $user, UsersRepository $usersRepository)
    {
        $this->translator = $translator;
        $this->user = $user;
        $this->usersRepository = $usersRepository;
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
            $locales[$l] = mb_strtoupper(mb_substr($l, 0, 2));
        }

        $locale = $form->addSelect('locale', null, $locales)
            ->setHtmlAttribute('onChange', 'submit()');
        $locale->getControlPrototype()->addAttributes([
            'class' => 'select2',
            'style' => 'width: 80px',
            'allowClear' => 'false',
        ]);

        $form->setDefaults([
            'locale' => $this->translator->getLocale(),
        ]);

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        if (!$this->user->isLoggedIn()) {
            $form->addError('admin.components.change_locale_form.invalid_submission');
        }
        $user = $this->usersRepository->find($this->user->getId());
        $this->usersRepository->update($user, [
            'locale' => $values->locale,
        ]);
        $this->onChange->__invoke();
    }
}
