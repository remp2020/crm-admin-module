<?php

namespace Crm\AdminModule\Presenters;

use Crm\AdminModule\Forms\ConfigFormFactory;
use Crm\ApplicationModule\Config\Repository\ConfigCategoriesRepository;

class ConfigAdminPresenter extends AdminPresenter
{
    /** @var ConfigCategoriesRepository @inject */
    public $configCategoriesRepository;

    public function renderDefault($categoryId)
    {
        list($categories, $actualCategory) = $this->loadConfigs();

        $this->template->actualCategory = $actualCategory;
        $this->template->categories = $categories;
    }

    private function loadConfigs()
    {
        $categories = $this->configCategoriesRepository->all();

        $actualCategory = false;
        if (isset($this->params['categoryId'])) {
            foreach ($categories as $category) {
                if ($category->id == $this->params['categoryId']) {
                    $actualCategory = $category;
                }
            }
        }
        if (!$actualCategory) {
            $actualCategory = $categories->fetch();
        }
        return [$categories, $actualCategory];
    }

    public function createComponentConfigForm(ConfigFormFactory $configFormFactory)
    {
        list($categories, $actualCategory) = $this->loadConfigs();

        $form = $configFormFactory->create($actualCategory);
        $configFormFactory->onSave = function ($categoryId) {
            $this->flashMessage($this->translator->translate('admin.admin.configs.messages.settings_saved'));
            $this->redirect('ConfigAdmin:default', $categoryId);
        };
        return $form;
    }

    public function actionChangeDefaultLocale($locale, $back)
    {
        $this->localeSession->setLocale($locale);
        $this->restoreRequest($back);
    }
}
