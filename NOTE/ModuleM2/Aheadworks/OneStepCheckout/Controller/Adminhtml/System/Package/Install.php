<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Controller\Adminhtml\System\Package;

use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\Composer\PackageInstaller;
use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\DatabaseDownloader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;

/**
 * Class Install
 * @package Aheadworks\OneStepCheckout\Controller\Adminhtml\System\Package
 */
class Install extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_OneStepCheckout::config_aw_osc';

    /**
     * @var PackageInstaller
     */
    private $packageInstaller;

    /**
     * @var DatabaseDownloader
     */
    private $databaseDownloader;

    /**
     * @param Context $context
     * @param PackageInstaller $packageInstaller
     * @param DatabaseDownloader $databaseDownloader
     */
    public function __construct(
        Context $context,
        PackageInstaller $packageInstaller,
        DatabaseDownloader $databaseDownloader
    ) {
        parent::__construct($context);
        $this->packageInstaller = $packageInstaller;
        $this->databaseDownloader = $databaseDownloader;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $requestData = $this->getRequest()->getPostValue();
        $responseData = [];
        if ($this->isValid($requestData)) {
            set_time_limit(0);

            try {
                $this->packageInstaller->install(
                    $requestData['package_name'],
                    $requestData['package_version']
                );
                $responseData['success'] = true;
            } catch (\Exception $e) {
                $responseData['success'] = false;
                $responseData['error'] = $e->getMessage();
            }

            try {
                if ($this->isValidForDownload($requestData)) {
                    $info = $this->databaseDownloader->downloadAndUnpack(
                        $requestData['database_download_url'],
                        $requestData['database_file_name']
                    );
                    $responseData['database_downloaded'] = true;
                    $responseData['database_updated_at'] = $info['modified_at'];
                }
            } catch (\Exception $e) {
                $responseData['database_downloaded'] = false;
            }

            ini_restore('max_execution_time');
        } else {
            $responseData['success'] = false;
        }

        return $resultJson->setData($responseData);
    }

    /**
     * Check if request data is valid
     *
     * @param array $requestData
     * @return bool
     */
    private function isValid($requestData)
    {
        return isset($requestData['package_name'])
            && isset($requestData['package_version']);
    }

    /**
     * Check if request data valid for download database
     *
     * @param array $requestData
     * @return bool
     */
    private function isValidForDownload($requestData)
    {
        return isset($requestData['database_download_url'])
            && isset($requestData['database_file_name']);
    }
}
