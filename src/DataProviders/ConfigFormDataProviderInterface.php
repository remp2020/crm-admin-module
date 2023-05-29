<?php
declare(strict_types=1);

namespace Crm\AdminModule\DataProvider;

use Crm\ApplicationModule\DataProvider\DataProviderInterface;
use Nette\Application\UI\Form;

interface ConfigFormDataProviderInterface extends DataProviderInterface
{
    public function provide(array $params): Form;
}
