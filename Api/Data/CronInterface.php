<?php

namespace Aiops\Monitoring\Api\Data;

interface CronInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @return mixed
     */
    public function getJobCode();

    /**
     * @return mixed
     */
    public function getMessages();

    /**
     * @return mixed
     */
    public function getScheduledAt();

    /**
     * @return mixed
     */
    public function getExecutedAt();

    /**
     * @return mixed
     */
    public function getFinishedAt();

    /**
     * @return mixed
     */
    public function getCronExprArr();

    /**
     * @return string
     */
    public function getCreatedAt();
}
