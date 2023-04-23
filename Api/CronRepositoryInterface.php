<?php

/**
 *
 * @author    AIOPS Group <developer@aiopsgroup.com>
 * @copyright 2023 AIOPS Group Support
 */

namespace Aiops\Monitoring\Api;

interface CronRepositoryInterface
{
    /**
     * @param int $cronId
     * @return \Aiops\Monitoring\Api\Data\CronSearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($cronId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aiops\Monitoring\Api\Data\CronSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
