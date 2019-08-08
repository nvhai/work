<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Customer\Api\CustomerRepositoryInterface;
use IWD\BluePaySubs\Api\SubscriptionRepositoryInterface;
use IWD\BluePaySubs\Controller\Adminhtml\Subscription;
use IWD\BluePaySubs\Model\Service\Subscription as ServiceSubscription;

class Synchronize extends Subscription
{
    /**
     * @var \IWD\BluePaySubs\Model\Source\Status
     */
    protected $statusSource;

    /**
     * @var ServiceSubscription
     */
    protected $serviceSubscription;

    /**
     * @inheritDoc
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        SubscriptionRepositoryInterface $subscriptionRepository,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \IWD\BluePaySubs\Helper\Data $helper,
        ServiceSubscription $serviceSubscription
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $registry,
            $subscriptionRepository,
            $customerRepository,
            $resultLayoutFactory,
            $helper
        );

        $this->serviceSubscription = $serviceSubscription;
    }

    /**
     * Subscription status-change action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $this->_init();

            /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
            $subscription = $this->registry->registry('current_bsubs');

            $this->serviceSubscription->synchronize($subscription);

//            $this->messageManager->addSuccessMessage(__('Subscription synchronized. '));
            $resultRedirect->setPath('*/*/edit', ['entity_id' => $subscription->getId(), '_current' => true]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        empty($subscription) ? $resultRedirect->setPath('*/*/index') :
            $resultRedirect->setPath('*/*/edit', ['entity_id' => $subscription->getId(), '_current' => true]);

        return $resultRedirect;
    }
}