<?php

namespace Aiops\Monitoring\Model\Api;

use Aiops\Monitoring\Helper\Splunk;
use Exception;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response;
use Magento\Framework\Filesystem\Io\File as Io;
use Magento\Framework\Archive\Helper\File\Gz as Gz;
use Magento\Framework\File\Size;

class Log extends Gz
{
    const LOGS_NOT_FOUND = "No Logs Files found in log directory";

    const RANGE_ERROR = "Please provide Byte Range in Correct Format like 10-500 or 500-2500 etc.";
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
     * @var Io
     */
    protected Io $io;

    /**
     * @var Size
     */
    protected Size $size;

    /**
     * @var RawFactory
     */
    protected RawFactory $resultRawFactory;

    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * @param DirectoryList $directoryList
     * @param File $driverFile
     * @param RawFactory $resultRawFactory
     * @param FileFactory $fileFactory
     * @param Splunk $splunk
     * @param Request $request
     * @param Response $response
     * @param Io $io
     * @param Size $size
     */
    public function __construct(
        DirectoryList   $directoryList,
        File            $driverFile,
        RawFactory      $resultRawFactory,
        FileFactory     $fileFactory,
        Splunk          $splunk,
        Request         $request,
        Response        $response,
        Io              $io,
        Size            $size
    ) {
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileFactory = $fileFactory;
        $this->splunk = $splunk;
        $this->request = $request;
        $this->response = $response;
        $this->io = $io;
        $this->size = $size;
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
                $this->splunk->setLogInfo(Splunk::MONITORING_ENABLED_MESSAGE);
                return Splunk::MONITORING_ENABLED_MESSAGE;
            }

            if (!preg_match("/^\d+-\d+$/", $range)) {
                return self::RANGE_ERROR;
            }

            if ($filePath == '') {
                return "Please provide correct file name.";
            }
            if (!$range) {
                return self::RANGE_ERROR;
            }
            return $this->getFileContents($filePath, $range);
        } catch (Exception $e) {
            $this->splunk->setLogCritical($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * @param string $filePath
     * @param string $range
     * @return string
     * @throws FileSystemException
     */
    protected function getFileContents(string $filePath, string $range)
    {
        list($seek_start, $seek_end) = explode('-', $range, 2);
        $size = filesize($filePath);
        $seek_end = (empty($seek_end)) ? ($size - 1) : min(abs((int)$seek_end), ($size - 1));
        $seek_start = (empty($seek_start) || $seek_end < abs((int)$seek_start)) ? 0 : max(abs((int)$seek_start), 0);

        $fileExt = $this->io->getPathInfo($filePath);
        $readLength = (int)$seek_end - (int)$seek_start;
        $maxFileSize = $this->size->getMaxFileSize();
        if ($readLength >= $maxFileSize) {
            $readLength = $maxFileSize;
        }

        if ($fileExt == 'gz') {
            $fileHandle = Gz::_open($filePath, 'rb');
            $this->driverFile->fileSeek($fileHandle, $seek_start);
            $content = Gz::_read($fileHandle, $readLength);
            Gz::_close($fileHandle);
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
                $this->splunk->setLogInfo(Splunk::MONITORING_ENABLED_MESSAGE);
                return Splunk::MONITORING_ENABLED_MESSAGE;
            }
            $directoryPath = $this->directoryList->getPath('log');
            if (!$this->driverFile->isExists($directoryPath)) {
                return "Logs Directory Doesn't Exist";
            }
            $files = $this->driverFile->readDirectoryRecursively($directoryPath);
            if (count($files) < 1) {
                $this->splunk->setLogInfo(self::LOGS_NOT_FOUND);
                return self::LOGS_NOT_FOUND;
            }
            foreach ($files as $file) {
                if ($this->driverFile->isFile($file)) {
                    $list[] = $this->formatLogsResponse($file);
                }
            }
        } catch (Exception|FileSystemException $e) {
            $this->splunk->setLogCritical($e->getMessage());
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
