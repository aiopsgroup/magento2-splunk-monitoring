<?php

namespace Aiops\Monitoring\Api;

interface LogInterface
{
    /**
     * Get logs
     *
     * @param string $filePath
     * @param string $range
     * @return mixed
     */
    public function getLog($filePath, $range);

    /**
     * List of Logs File api
     *
     * @return string
     */
    public function getListoflogs();
}
