<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Class Info
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\File
 */
class Info
{
    /**
     * Base path
     */
    const BASE_PATH = 'aw_osc/geo_ip';

    /**
     * @var ReadInterface
     */
    private $mediaDirectory;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * Get absolute path to file
     *
     * @param string $path
     * @return string
     */
    public function getAbsolutePath($path = '')
    {
        return $this->mediaDirectory->getAbsolutePath($this->getFilePath($path));
    }

    /**
     * Get file path
     *
     * @param string $path
     * @return string
     */
    private function getFilePath($path)
    {
        return self::BASE_PATH . ($path == '' ? $path : '/' . trim($path, '/'));
    }

    /**
     * Check if file exist
     *
     * @param string $path
     * @return bool
     */
    public function isExist($path)
    {
        return $this->mediaDirectory->isExist($this->getFilePath($path));
    }

    /**
     * Get modification timestamp
     *
     * @param string $path
     * @return int
     */
    public function getModificationTimestamp($path)
    {
        return filemtime($this->getAbsolutePath($path));
    }
}
