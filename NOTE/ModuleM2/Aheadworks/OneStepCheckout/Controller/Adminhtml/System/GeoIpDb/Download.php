<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Controller\Adminhtml\System\GeoIpDb;

use Aheadworks\OneStepCheckout\Model\Address\Form\GeoIp\DatabaseDownloader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;

/**
 * Class Download
 * @package Aheadworks\OneStepCheckout\Controller\Adminhtml\System\GeoIpDb
 */
class Download extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_OneStepCheckout::config_aw_osc';

    /**
     * @var DatabaseDownloader
     */
    private $downloader;

    /**
     * @param Context $context
     * @param DatabaseDownloader $downloader
     */
    public function __construct(
        Context $context,
        DatabaseDownloader $downloader
    ) {
        parent::__construct($context);
        $this->downloader = $downloader;
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
                $info = $this->downloader->downloadAndUnpack(
                    $requestData['download_url'],
                    $requestData['file_name']
                );
                $responseData['success'] = true;
                $responseData['updated_at'] = $info['modified_at'];
            } catch (\Exception $e) {
                $responseData['success'] = false;
                $responseData['error'] = $e->getMessage();
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
        return isset($requestData['download_url'])
            && isset($requestData['file_name']);
    }
}
