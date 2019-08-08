<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Bsubs;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action;

/**
 * Class Index
 * @package IWD\BluePaySubs\Controller\Bsubs
 */
class Index extends Action\Action
{
    /**
     * Subscriptions index page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Subscriptions'));

        return $resultPage;
    }
}
