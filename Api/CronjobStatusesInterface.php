<?php

namespace Aiops\Monitoring\Api;

interface CronjobStatusesInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aiops\Monitoring\Api\Data\CronSearchResultInterface
     */
    public function getCronjobStatuses(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @return array
     */
    public function getCronjobList();
}
