<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Adminhtml\Subscription;

use IWD\BluePaySubs\Controller\Adminhtml\Subscription;

/**
 * Subscriptions form
 */
class Edit extends Subscription
{
    /**
     * Subscriptions list action
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            $this->_init();

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        }

        $resultPage = $this->resultPageFactory->create();

        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('IWD_BluePaySubs::bsubs_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Subscription'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Subscription'), __('Subscription'));
        $resultPage->addBreadcrumb(__('Manage Subscription'), __('Manage Subscription'));

        return $resultPage;
    }
}
