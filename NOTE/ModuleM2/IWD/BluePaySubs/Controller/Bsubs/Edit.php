<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Controller\Bsubs;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use IWD\BluePaySubs\Api\RebillManagementInterface;

/**
 * Edit Class
 */
class Edit extends \IWD\BluePaySubs\Controller\Bsubs
{
    /**
     * @var \IWD\BluePaySubs\Model\Source\Status
     */
    protected $statusSource;

    /**
     * Edit constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param RebillManagementInterface $rebillManagement
     * @param \IWD\BluePaySubs\Model\Source\Status $statusSource
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        RebillManagementInterface $rebillManagement,
        \IWD\BluePaySubs\Model\Source\Status $statusSource
    ) {
        parent::__construct($context, $registry, $subscriptionRepository, $currentCustomer, $rebillManagement);
        $this->statusSource = $statusSource;
    }

    /**
     * Subscriptions edit page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $initialized = $this->_init();

        if ($initialized !== true) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        }
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->registry->registry('current_subs');

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('My Subscriptions'));
        $resultPage->getConfig()->getTitle()->prepend($subscription->getDescription());
        $resultPage->getConfig()->getTitle()->prepend(__('Edit'));

        /** @var \Magento\Theme\Block\Html\Title $titleBlock */
        $titleBlock = $resultPage->getLayout()->getBlock('page.main.title');
        if ($titleBlock) {
            $titleBlock->setPageTitle($subscription->getDescription());
            $statusBlock = $titleBlock->getChildBlock('subs.status');
            if($statusBlock) {
                $status = $subscription->getStatus();
                $statusBlock->setStatus($status)
                    ->setStatusText($this->statusSource->getOptionText($status));
            }
        }

        return $resultPage;
    }
}
