<?php

namespace Crm\AdminModule\Models;

use Crm\ApplicationModule\Models\DataProvider\DataProviderInterface;

interface UniversalSearchDataProviderInterface extends DataProviderInterface
{
    /***
     * @param array{term: string} $params {
     *   Associative array contains key 'term' with search term value.
     *   e.g. ['term' => 'example']
     * }
     * @return array{groupName: string, array{id: string, text: string, url: string}}
     *  Associative array contains key 'groupName' which determine the group to which the search results belong.
     *  Search results array contains keys:
     *  - id: Unique identifier
     *  - text: String displayed in result options dropdown
     *  - url: URL to redirect after selecting an option
     *
     *   e.g: [
     *          'users' => [
     *              'id' => 'user_1',
     *              'text' => 'User 1',
     *              'url' => 'http://example.com'
     *              ],
     *         'payments' => [
     *              'id' => 'payment_1',
     *              'text' => 'Payment 1',
     *              'url' => 'http://example.com'
     *              ]
     *        ]
     *
     */
    public function provide(array $params): array;
}
