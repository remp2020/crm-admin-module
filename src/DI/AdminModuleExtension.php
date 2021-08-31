<?php

namespace Crm\AdminModule\DI;

use Kdyby\Translation\DI\ITranslationProvider;
use Nette\DI\CompilerExtension;

final class AdminModuleExtension extends CompilerExtension implements ITranslationProvider
{
    public function loadConfiguration()
    {
        // load services from config and register them to Nette\DI Container
        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__.'/../config/config.neon')['services']
        );
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        // load presenters from extension to Nette
        $builder->getDefinition($builder->getByType(\Nette\Application\IPresenterFactory::class))
            ->addSetup('setMapping', [['Admin' => 'Crm\AdminModule\Presenters\*Presenter']]);
    }

    /**
     * Return array of directories, that contain resources for translator.
     * @return string[]
     */
    public function getTranslationResources()
    {
        return [__DIR__ . '/../lang/'];
    }
}
