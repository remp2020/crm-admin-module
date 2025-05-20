<?php

namespace Crm\AdminModule\Models;

use Crm\ApplicationModule\Models\DataProvider\DataProviderManager;

class UniversalSearch
{
    private DataProviderManager $dataProviderManager;

    public function __construct(
        DataProviderManager $dataProviderManager,
    ) {
        $this->dataProviderManager = $dataProviderManager;
    }

    public function search(string $term)
    {
        $term = trim($term);

        $searchResults = [];
        /** @var UniversalSearchDataProviderInterface[] $providers */
        $providers = $this->dataProviderManager->getProviders('admin.dataprovider.universal_search', UniversalSearchDataProviderInterface::class);
        foreach ($providers as $provider) {
            $searchResults[] = $provider->provide(['term' => $term]);
        }

        return $this->mergeSearchResults($searchResults);
    }

    private function mergeSearchResults($searchResults)
    {
        $finalResult = [];
        foreach ($searchResults as $searchResult) {
            foreach ($searchResult as $group => $items) {
                $this->addItemsToResult($group, $items, $finalResult);
            }
        }

        return $finalResult;
    }

    private function addItemsToResult($groupName, $items, &$array)
    {
        foreach ($array as $group) {
            if ($group['text'] === $groupName) {
                $group['children'] = array_merge($group['children'], $items);
                return;
            }
        }

        $array[] = [
            'text' => $groupName,
            'children' => $items,
        ];
    }
}
