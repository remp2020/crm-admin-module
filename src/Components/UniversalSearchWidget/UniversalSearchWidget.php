<?php

namespace Crm\AdminModule\Components\UniversalSearchWidget;

use Crm\AdminModule\Model\UniversalSearch;
use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;

class UniversalSearchWidget extends BaseWidget
{
    private string $templatePath = __DIR__ . DIRECTORY_SEPARATOR . 'universal_search_widget.latte';
    private UniversalSearch $universalSearch;

    public function __construct(
        WidgetManager $widgetManager,
        UniversalSearch $universalSearch
    ) {
        parent::__construct($widgetManager);
        $this->universalSearch = $universalSearch;
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
            "results" => $result
        ]);
    }
}
