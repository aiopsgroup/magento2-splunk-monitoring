<?php

namespace Aiops\Monitoring\Model\Api;

use Aiops\Monitoring\Api\CronjobStatusesInterface;
use Aiops\Monitoring\Api\Data\CronSearchResultInterface;
use Aiops\Monitoring\Model\CronRepository;
use Exception;
use Magento\Cron\Model\ConfigInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as ScopeInterfaceAlias;
use Psr\Log\LoggerInterface;
use Aiops\Monitoring\Block\Splunk;

class CronjobStatuses implements CronjobStatusesInterface
{
    private CronRepository $cronRepository;
    private LoggerInterface $logger;
    private ConfigInterface $config;
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Splunk
     */
    protected Splunk $splunk;

    /**
     * @param CronRepository $cronRepository
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $config
     * @param Splunk $splunk
     */
    public function __construct(
        CronRepository       $cronRepository,
        LoggerInterface      $logger,
        ScopeConfigInterface $scopeConfig,
        ConfigInterface      $config,
        Splunk               $splunk
    ) {
        $this->cronRepository = $cronRepository;
        $this->logger = $logger;
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->splunk      = $splunk;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return CronSearchResultInterface|string
     */
    public function getCronjobStatuses(SearchCriteriaInterface $searchCriteria)
    {
        try {
            if (!$this->splunk->isMonitoringEnabled()) {
                return "Please enable and configure Splunk Monitoring Module";
            }
            return $this->cronRepository->getList($searchCriteria);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * @return array
     */
    public function getCronjobList()
    {

        $data = [];

        if (!$this->splunk->isMonitoringEnabled()) {
            return "Please enable and configure Splunk Monitoring Module";
        }

        $jobs = $this->config->getJobs();

        foreach ($jobs as $jobGroupCode => $jobGroup) {
            foreach ($jobGroup as $jobKey => $jobConfig) {
                $data[] = [
                    'job' => $jobGroup['name'] ?? $jobKey,
                    'group' => $jobGroupCode,
                    'schedule' => $this->getCronExpression($jobConfig)
                ];
            }
        }

        usort($data, static function ($a, $b) {
            return strcmp($a['job'], $b['job']);
        });

        return $data;
    }

    /**
     * Get cron expression of cron job.
     *
     * @param array $jobConfig
     * @return null|string
     */
    private function getCronExpression($jobConfig)
    {
        $cronExpression = null;

        if (isset($jobConfig['config_path'])) {
            $cronExpression = $this->getConfigSchedule($jobConfig) ?: null;
        }

        if (!$cronExpression && isset($jobConfig['schedule'])) {
            $cronExpression = $jobConfig['schedule'];
        }

        return $cronExpression;
    }

    /**
     * Get config of schedule.
     *
     * @param array $jobConfig
     * @return mixed
     */
    private function getConfigSchedule($jobConfig)
    {
        return $this->scopeConfig->getValue(
            $jobConfig['config_path'],
            ScopeInterfaceAlias::SCOPE_STORE
        );
    }
}
