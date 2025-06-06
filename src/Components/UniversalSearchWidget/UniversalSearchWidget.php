<?php

namespace Crm\AdminModule\Components\UniversalSearchWidget;

use Crm\AdminModule\Models\UniversalSearch;
use Crm\ApplicationModule\Models\Widget\BaseLazyWidget;
use Crm\ApplicationModule\Models\Widget\LazyWidgetManager;

class UniversalSearchWidget extends BaseLazyWidget
{
    private string $templatePath = __DIR__ . DIRECTORY_SEPARATOR . 'universal_search_widget.latte';

    public function __construct(
        LazyWidgetManager $lazyWidgetManager,
        private UniversalSearch $universalSearch,
    ) {
        parent::__construct($lazyWidgetManager);
    }

    public function identifier()
    {
        return 'universalsearchwidget';
    }

    public function render()
    {
        $this->template->url = $this->link('search');
        $this->template->setFile($this->templatePath);
        $this->template->render();
    }

    public function handleSearch()
    {
        $term = $this->getPresenter()->getParameter('q');
        $result = $this->universalSearch->search($term);

        $this->getPresenter()->sendJson([
            "results" => $result,
        ]);
    }
}
