<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\File;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Class Downloader
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\File
 */
class Downloader
{
    /**
     * @var DriverPool
     */
    private $driverPool;

    /**
     * @var Info
     */
    private $fileInfo;

    /**
     * @param DriverPool $driverPool
     * @param Info $fileInfo
     */
    public function __construct(
        DriverPool $driverPool,
        Info $fileInfo
    ) {
        $this->driverPool = $driverPool;
        $this->fileInfo = $fileInfo;
    }

    /**
     * Download file into specific folder
     *
     * @param string $path
     * @param string $pathToSave Relative path
     * @return string
     * @throws FileSystemException
     */
    public function download($path, $pathToSave)
    {
        $httpDriver = $this->driverPool->getDriver(DriverPool::HTTP);
        $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
        if ($httpDriver->isExists($path)) {
            $absoluteBasePath = $this->fileInfo->getAbsolutePath();
            if (!$fileDriver->isExists($absoluteBasePath)) {
                $fileDriver->createDirectory($absoluteBasePath);
            }
            $httpDriver->copy($path, $this->fileInfo->getAbsolutePath($pathToSave), $fileDriver);
        }
    }
}
