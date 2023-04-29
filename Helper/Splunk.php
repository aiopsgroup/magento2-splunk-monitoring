<?php
declare(strict_types=1);

namespace Aiops\Monitoring\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Splunk extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const STATUS = 'monitoring/splunk/enabled';

    public const MONITORING_ENABLED_MESSAGE = "Please enable and configure Splunk Monitoring Module";

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @return int
     */
    public function isMonitoringEnabled()
    {
        return (int)$this->scopeConfig->getValue(self::STATUS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $message
     * @return void
     */
    public function setLogInfo($message)
    {
        $this->logger->info($message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function setLogCritical($message)
    {
        $this->logger->critical($message);
    }
}