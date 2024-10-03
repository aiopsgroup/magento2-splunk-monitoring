<?php
/**
 *
 * @author    AIOPS Group <aiopsmonitoring@aiopsgroup.com>
 * @copyright 2024 AIOPS Group Support
 */

namespace Aiops\Monitoring\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CronSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \Aiops\Monitoring\Api\Data\CronInterface[]
     */
    public function getItems();

    /**
     * @param \Aiops\Monitoring\Api\Data\CronInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
