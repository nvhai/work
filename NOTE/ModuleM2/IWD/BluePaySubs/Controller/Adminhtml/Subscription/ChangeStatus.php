<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Adminhtml\Subscription;

use IWD\BluePaySubs\Api\RebillManagementInterface;
use Magento\Backend\App\Action;
use Magento\Customer\Api\CustomerRepositoryInterface;
use IWD\BluePaySubs\Controller\Adminhtml\Subscription;
use IWD\BluePaySubs\Api\SubscriptionRepositoryInterface;
use IWD\BluePaySubs\Model\Service\Subscription as ServiceSubscription;

/**
 * ChangeStatus Class
 */
class ChangeStatus extends Subscription
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
     * @var RebillManagementInterface
     */
    protected $rebillManagement;

    /**
     * ChangeStatus constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \IWD\BluePaySubs\Helper\Data $helper
     * @param \IWD\BluePaySubs\Model\Source\Status $statusSource
     * @param ServiceSubscription $serviceSubscription
     * @param RebillManagementInterface $rebillManagement
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        SubscriptionRepositoryInterface $subscriptionRepository,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \IWD\BluePaySubs\Helper\Data $helper,
        \IWD\BluePaySubs\Model\Source\Status $statusSource,
        ServiceSubscription $serviceSubscription,
        RebillManagementInterface $rebillManagement
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

        $this->statusSource = $statusSource;
        $this->serviceSubscription = $serviceSubscription;
        $this->rebillManagement = $rebillManagement;
    }

    /**
     * Subscription status-change action
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $initialized = $this->_init();
            if ($initialized !== true) {
                $resultRedirect->setRefererOrBaseUrl();

                return $resultRedirect;
            }
            /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
            $subscription = $this->registry->registry('current_bsubs');
            $newStatus = $this->getRequest()->getParam('status');

            if ($rebillStatus = $this->rebillManagement->updateRebillStatus($subscription, $newStatus)) {
                $message = __(
                    'Subscription status changed to "%1".',
                    $this->statusSource->getOptionText($rebillStatus->getStatus())
                );
                $subscription->setStatus($rebillStatus->getStatus())->addLog($message);
                $this->subscriptionRepository->save($subscription);
                $this->messageManager->addSuccessMessage($message);
            }
            else {
                throw new \Exception(
                    __("Subscription not updated on payment gateway")
                );
            }

            $resultRedirect->setPath('*/*/edit', ['entity_id' => $subscription->getId(), '_current' => true]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('ERROR: %1', $e->getMessage()));
            $resultRedirect->setPath('*/*/edit', ['entity_id' => $subscription->getId(), '_current' => true]);
        }

        return $resultRedirect;
    }
}
