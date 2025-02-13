<?php
declare(strict_types=1);

namespace Crm\AdminModule\DataProviders;

use Crm\ApplicationModule\Models\DataProvider\DataProviderInterface;
use Crm\ApplicationModule\UI\Form;

interface ConfigFormDataProviderInterface extends DataProviderInterface
{
    public function provide(array $params): Form;
}
