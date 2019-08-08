<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Adminhtml\Subscription;

use IWD\BluePaySubs\Controller\Adminhtml\Subscription;
/**
 * Subscriptions history tab/grid AJAX handler
 */
class LogGrid extends Subscription
{
    /**
     * Subscription logs grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        try {
            $this->_init();
            $resultLayout = $this->resultLayoutFactory->create();
            return $resultLayout;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        }
    }
}
