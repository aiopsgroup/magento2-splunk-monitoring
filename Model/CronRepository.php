<?php
declare(strict_types=1);
/**
 *
 * @author    AIOPS Group <developer@aiopsgroup.com>
 * @copyright 2023 AIOPS Group Support
 */

namespace Aiops\Monitoring\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Aiops\Monitoring\Api\Data\CronSearchResultInterfaceFactory;
use Magento\Cron\Model\ResourceModel\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory;

class CronRepository
{
    /**
     * @var ScheduleFactory
     */
    private $cronFactory;

    /**
     * @var Schedule
     */
    private $cronResource;

    /**
     * @var CollectionFactory
     */
    private $cronCollectionFactory;

    /**
     * @var CronSearchResultInterfaceFactory
     */
    private $searchResultFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        ScheduleFactory                  $cronFactory,
        Schedule                         $cronResource,
        CollectionFactory                $cronCollectionFactory,
        CronSearchResultInterfaceFactory $cronSearchResultInterfaceFactory,
        CollectionProcessorInterface     $collectionProcessor
    ) {
        $this->cronFactory = $cronFactory;
        $this->cronResource = $cronResource;
        $this->cronCollectionFactory = $cronCollectionFactory;
        $this->searchResultFactory = $cronSearchResultInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param int $id
     * @return \Magento\Cron\Model\Schedule
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $cron = $this->cronFactory->create();
        $this->cronResource->load($cron, $id);
        if (!$cron->getId()) {
            throw new NoSuchEntityException(__('Unable to find Cron job with ID "%1"', $id));
        }
        return $cron;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aiops\Monitoring\Api\Data\CronSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->cronCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria)
            ->setItems($collection->getItems())
            ->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
