<?php
declare(strict_types=1);

namespace Aiops\Monitoring\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Splunk extends Template
{
    const SPLUNK_URL = 'monitoring/splunk/url';

    const HEC_TOKEN = 'monitoring/splunk/hec';

    const SCHEDULE = 'monitoring/splunk/schedule';

    const STATUS = 'monitoring/splunk/enabled';

    const API_URL   =  'services/collector';

    const ENABLE_MODULE_MESSAGE = "Please enable and Configure Splunk Module";

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Curl
     */
    protected Curl $curl;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param Curl $curl
     */
    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        Curl $curl
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->curl = $curl;
        parent::__construct($context, []);
    }

    /**
     * Call curl request of Splunk.
     *
     * @param string $eventname
     * @param array $data
     * @return string
     */
    public function sendCurlRequest($eventname, $data)
    {
        $params = [
            'sourcetype' => $eventname,
            'event' => $data
        ];
        $splunkUrl = $this->getSplunkUrl();
        $hec_token = $this->getToken();
        $this->curl->addHeader("Authorization", "Splunk $hec_token");
        $this->curl->post($splunkUrl, json_encode($params));
        $response  = $this->curl->getBody();
        if (stripos($response, 'success') === false):
            $this->logger->info($response);
        endif;
        return $response;
    }

    /**
     * @return string
     */
    private function getSplunkUrl()
    {
        $splunk_url = $this->scopeConfig->getValue(self::SPLUNK_URL, ScopeInterface::SCOPE_STORE);
        return $splunk_url.'/'.self::API_URL;
    }


    /**
     * @return string
     */
    private function getToken()
    {
        return $this->scopeConfig->getValue(self::HEC_TOKEN, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getCronStartTime()
    {
        return $this->scopeConfig->getValue(self::SCHEDULE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function isMonitoringEnabled()
    {
        return (int)$this->scopeConfig->getValue(self::STATUS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $message
     * @return void
     */
    public function setLogInfo($message)
    {
        $this->logger->info($message);
    }

    /**
     * @param $message
     * @return void
     */
    public function setLogCritical($message)
    {
        $this->logger->critical($message);
    }
}
