<?php

namespace Crm\AdminModule\Forms;

use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Config\ConfigsCache;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Tracy\Debugger;

class ConfigFormFactory
{
    private $configsRepository;

    private $configsCache;

    private $translator;

    public $onSave;

    public function __construct(
        ConfigsRepository $configsRepository,
        ConfigsCache $configsCache,
        Translator $translator
    ) {
        $this->configsRepository = $configsRepository;
        $this->configsCache = $configsCache;
        $this->translator = $translator;
    }

    public function create($categoryId = null)
    {
        $form = new Form;
        $form->setRenderer(new BootstrapRenderer());
        $form->setTranslator($this->translator);
        $form->addProtection();

        $configs = $this->configsRepository->loadByCategory($categoryId);
        foreach ($configs as $config) {
            $item = null;
            if ($config->type == ApplicationConfig::TYPE_STRING) {
                $item = $form->addText($config->name, $config->display_name ?? $config->name);
            } elseif ($config->type == ApplicationConfig::TYPE_INT) {
                $item = $form->addText($config->name, $config->display_name ?? $config->name);
                $item->addCondition(Form::FILLED)
                    ->addRule(Form::INTEGER, $this->translator->translate('admin.admin.configs.validation.integer'));
            } elseif ($config->type == ApplicationConfig::TYPE_PASSWORD) {
                $item = $form->addText($config->name, $config->display_name ?? $config->name);
            } elseif ($config->type == ApplicationConfig::TYPE_TEXT) {
                $item = $form->addTextArea(
                    $config->name,
                    $config->display_name ?? $config->name
                )->setAttribute('rows', 5);
            } elseif ($config->type == ApplicationConfig::TYPE_HTML) {
                $item = $form->addTextArea(
                    $config->name,
                    $config->display_name ?? $config->name
                );
                $item
                    ->setAttribute('rows', 15)
                    ->getControlPrototype()->addAttributes(['class' => 'ace', 'data-lang' => 'html']);
            } elseif ($config->type == ApplicationConfig::TYPE_BOOLEAN) {
                $item = $form->addCheckbox($config->name, $config->display_name ?? $config->name);
            } else {
                Debugger::log('Unknown config type [' . $config->type . '] of config [' . $config->name . ']', Debugger::ERROR);
                continue;
            }

            $item->setDefaultValue($config->value)
                ->setOption('description', Html::el('span', ['class' => 'help-block'])->setHtml($this->translator->translate($config->description)));
        }

        $form->addHidden('categoryId', $categoryId);

        $form->addSubmit('send', 'system.save')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> ' . $this->translator->translate('system.save'));

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $categoryId = $values['categoryId'];
        unset($values['categoryId']);

        foreach ($values as $name => $value) {
            $config = $this->configsRepository->loadByName($name);
            if ($value !== $config->value) {
                $this->configsRepository->update($config, ['value' => $value]);
                $this->configsCache->add($name, $value);
            }
        }

        $this->onSave->__invoke($categoryId);
    }
}
