<?php
declare(strict_types=1);

namespace Aiops\Monitoring\Model;

use Magento\Framework\Api\SearchResults;
use Aiops\Monitoring\Api\Data\CronSearchResultInterface;

class CronSearchResult extends SearchResults implements CronSearchResultInterface
{

}
