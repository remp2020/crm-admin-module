<?php

namespace Crm\AdminModule\Forms;

use Contributte\Translation\Translator;
use Crm\AdminModule\DataProvider\ConfigFormDataProviderInterface;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Config\ConfigsCache;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Crm\ApplicationModule\DataProvider\DataProviderManager;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\Utils\Json;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Tracy\Debugger;

class ConfigFormFactory
{
    public $onSave;

    public function __construct(
        private ConfigsRepository $configsRepository,
        private ConfigsCache $configsCache,
        private DataProviderManager $dataProviderManager,
        private Translator $translator
    ) {
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
            if ($config->type === ApplicationConfig::TYPE_STRING) {
                $item = $form->addText($config->name, $config->display_name ?? $config->name);
            } elseif ($config->type === ApplicationConfig::TYPE_INT) {
                $item = $form->addText($config->name, $config->display_name ?? $config->name);
                $item->addCondition(Form::FILLED)
                    ->addRule(Form::INTEGER, $this->translator->translate('admin.admin.configs.validation.integer'));
            } elseif ($config->type === ApplicationConfig::TYPE_PASSWORD) {
                $item = $form->addText($config->name, $config->display_name ?? $config->name);
            } elseif ($config->type === ApplicationConfig::TYPE_TEXT) {
                $item = $form->addTextArea(
                    $config->name,
                    $config->display_name ?? $config->name
                )->setHtmlAttribute('rows', 5);
            } elseif ($config->type === ApplicationConfig::TYPE_HTML) {
                $item = $form->addTextArea(
                    $config->name,
                    $config->display_name ?? $config->name
                );
                $item
                    ->setHtmlAttribute('rows', 15)
                    ->getControlPrototype()->addAttributes(['class' => 'ace', 'data-lang' => 'html']);
            } elseif ($config->type === ApplicationConfig::TYPE_BOOLEAN) {
                $item = $form->addCheckbox($config->name, $config->display_name ?? $config->name);
            } elseif ($config->type === ApplicationConfig::TYPE_SELECT) {
                $selectOptions = Json::decode($config->options, true);
                foreach ($selectOptions as $value => $label) {
                    $selectOptions[$value] = $this->translator->translate($label);
                }
                $item = $form->addSelect($config->name, $config->display_name ?? $config->name, $selectOptions);
            } else {
                Debugger::log('Unknown config type [' . $config->type . '] of config [' . $config->name . ']', Debugger::ERROR);
                continue;
            }

            $item->setDefaultValue($config->value)
                ->setOption('description', Html::el('span', ['class' => 'help-block'])->setHtml($this->translator->translate($config->description)));
        }

        $form->addHidden('categoryId', $categoryId);

        /** @var ConfigFormDataProviderInterface[] $providers */
        $providers = $this->dataProviderManager->getProviders('admin.dataprovider.config_form', ConfigFormDataProviderInterface::class);
        foreach ($providers as $sorting => $provider) {
            $form = $provider->provide(['form' => $form]);
        }

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
