<?php

namespace Aiops\Monitoring\Model\Api;

use Aiops\Monitoring\Block\Splunk;
use Exception;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Cron\Model\ScheduleFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response;

class Log
{
    const MAX_FILE_SIZE_RETRIEVE = 2097152; //2MB File Size
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Splunk
     */
    protected Splunk $splunk;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;

    /**
     * @var File
     */
    protected File $driverFile;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var RawFactory
     */
    protected RawFactory $resultRawFactory;

    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * @var ScheduleFactory
     */
    protected ScheduleFactory $_scheduleFactory;

    /**
     * Constructor.
     * @param DirectoryList $directoryList
     * @param File $driverFile
     * @param RawFactory $resultRawFactory
     * @param FileFactory $fileFactory
     * @param ScheduleFactory $scheduleFactory
     * @param LoggerInterface $logger
     * @param Splunk $splunk
     * @param Request $request
     * @param Response $response
     */
    public function __construct(
        DirectoryList   $directoryList,
        File            $driverFile,
        RawFactory      $resultRawFactory,
        FileFactory     $fileFactory,
        ScheduleFactory $scheduleFactory,
        LoggerInterface $logger,
        Splunk          $splunk,
        Request         $request,
        Response        $response
    ) {
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileFactory = $fileFactory;
        $this->_scheduleFactory = $scheduleFactory;
        $this->logger = $logger;
        $this->splunk = $splunk;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param string $filePath
     * @param string $range
     * @return int|string
     */
    public function getLog($filePath, $range)
    {
        try {
            if (!$this->splunk->isMonitoringEnabled()) {
                return "Please enable and configure Splunk Monitoring Module";
            }

            if (!preg_match("/^\d+-\d+$/", $range)) {
                return "The 'range' parameter is either missing or has an incorrect format";
            }

            if ($filePath == '') {
                return "Please provide correct file name.";
            }
            if (!$range) {
                return "Please provide Byte Range in Correct Format like 10-500 or 500-2500 etc.";
            }
            return $this->getFileContents($filePath, $range);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * @param $filePath
     * @param $range
     * @return string|int
     * @throws FileSystemException
     */
    protected function getFileContents($filePath, $range)
    {
        list($seek_start, $seek_end) = explode('-', $range, 2);
        $size = filesize($filePath);
        $seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));
        $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

        $fileExt = pathinfo($filePath, PATHINFO_EXTENSION);
        $readLength = (int)$seek_end - (int)$seek_start;

        if($readLength >= Log::MAX_FILE_SIZE_RETRIEVE) {
            $readLength = Log::MAX_FILE_SIZE_RETRIEVE;
        }

        if ($fileExt == 'gz') {
            $fileHandle = gzopen($filePath, 'rb');
            fSeek($fileHandle, $seek_start);
            $content = gzread($fileHandle, $readLength);
            gzclose($fileHandle);
        } else {
            $fileHandle = $this->driverFile->fileOpen($filePath, 'rb');
            $this->driverFile->fileSeek($fileHandle, $seek_start);
            $content = $this->driverFile->fileRead($fileHandle, $readLength);
            $this->driverFile->fileClose($fileHandle);

        }
        return $content;
    }

    /**
     * @return mixed
     */
    public function getListoflogs()
    {
        $list = [];
        try {
            if (!$this->splunk->isMonitoringEnabled()) {
                return "Please enable and configure Splunk Monitoring Module";
            }
            $directoryPath = $this->directoryList->getPath('log');
            if (!$this->driverFile->isExists($directoryPath)) {
                return "Logs Directory Doesn't Exist";
            }
            $files = $this->driverFile->readDirectoryRecursively($directoryPath);
            if (count($files) < 1) {
                return "No Logs Files found in log directory";
            }
            foreach ($files as $file) {
                if ($this->driverFile->isFile($file)) {
                    $list[] = $this->formatLogsResponse($file);
                }
            }
        } catch (Exception|FileSystemException $e) {
            $this->logger->error($e->getMessage());
            $list = $e->getMessage();
        }
        return $list;
    }

    /**
     * @param $file
     * @return array
     */
    private function formatLogsResponse($file): array
    {
        $arrayResponse['displayname'] = basename($file);
        $arrayResponse['size'] = filesize($file);
        $arrayResponse['path'] = $file;
        $arrayResponse['type'] = mime_content_type($file);
        $arrayResponse['modified_time'] = date("Y/m/d H:i:s", filemtime($file));
        $arrayResponse['creation_time'] = date("Y/m/d H:i:s", filectime($file));
        return $arrayResponse;
    }
}
