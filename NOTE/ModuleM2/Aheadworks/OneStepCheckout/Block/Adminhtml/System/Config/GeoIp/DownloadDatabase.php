<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\GeoIp;

use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer\PackageInfo;
use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\File\Info;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class DownloadDatabase
 *
 * @method string getButtonLabel()
 * @method string getButtonLabelDownloaded()
 * @method string getSubmitPath()
 * @method string getDownloadUrl()
 * @method string getPackageName()
 * @method string getFileName()
 *
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\GeoIp
 */
class DownloadDatabase extends Field
{
    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var Info
     */
    private $fileInfo;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'system/config/geo_ip/download_database.phtml';

    /**
     * @param Context $context
     * @param PackageInfo $packageInfo
     * @param Info $fileInfo
     * @param array $data
     */
    public function __construct(
        Context $context,
        PackageInfo $packageInfo,
        Info $fileInfo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->packageInfo = $packageInfo;
        $this->fileInfo = $fileInfo;
    }

    /**
     * Check if disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->isLibInstalled();
    }

    /**
     * Get submit url
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl($this->getSubmitPath());
    }

    /**
     * Check if library package installed
     *
     * @return bool
     */
    private function isLibInstalled()
    {
        return $this->packageInfo->isInstalled($this->getPackageName());
    }

    /**
     * Check if downloaded
     *
     * @return bool
     */
    public function isDownloaded()
    {
        return $this->fileInfo->isExist($this->getFileName());
    }

    /**
     * Check if downloaded and library package installed
     *
     * @return bool
     */
    public function isDownloadedAndLibInstalled()
    {
        return $this->isDownloaded() && $this->isLibInstalled();
    }

    /**
     * Get last updated datetime
     *
     * @return string
     */
    public function getLastUpdatedAt()
    {
        $timestamp = $this->fileInfo->getModificationTimestamp($this->getFileName());
        return $this->_localeDate->formatDateTime((new \DateTime())->setTimestamp($timestamp));
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label_downloaded' => $originalData['button_label_downloaded'],
                'submit_path' => $originalData['submit_path'],
                'download_url' => $originalData['download_url'],
                'package_name' => $originalData['package_name'],
                'file_name' => $originalData['file_name'],
            ]
        )->addData(
            [
                'button_label' => $this->isDownloaded()
                    ? $originalData['button_label_downloaded']
                    : $originalData['button_label_not_downloaded']
            ]
        );
        return $this->_toHtml();
    }
}
