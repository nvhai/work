<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Adminhtml\Subscription;

use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Api\RebillManagementInterface;
use IWD\BluePaySubs\Api\SubscriptionRepositoryInterface;
use Magento\Backend\App\Action;
use IWD\BluePaySubs\Controller\Adminhtml\Subscription;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Save Class
 */
class Save extends Subscription
{
    /**
     * @var \IWD\BluePaySubs\Model\Service\Subscription
     */
    protected $subscriptionService;

    /**
     * @var RebillManagementInterface
     */
    protected $rebillManagement;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \IWD\BluePaySubs\Helper\Data $helper
     * @param \IWD\BluePaySubs\Model\Service\Subscription $subscriptionService
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
        \IWD\BluePaySubs\Model\Service\Subscription $subscriptionService,
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

        $this->subscriptionService = $subscriptionService;
        $this->rebillManagement = $rebillManagement;
    }

    /**
     * Subscription save action
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $this->_init();

            /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
            $subscription = $this->registry->registry('current_bsubs');
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $subscription->getQuote();
            $data = $this->getRequest()->getParams();
            $subscription->setDataChanges(false);

            /**
             * Update payment
             */
            $this->subscriptionService->changePaymentAccount($subscription, $data['public_hash']);

            /**
             * Update shipping address
             */
            if ($quote->getIsVirtual() == false) {
                $this->subscriptionService->changeShippingAddress($subscription, $data['shipping']);
            }

            if ($subscription->hasDataChanges()) {
                if ($this->rebillManagement->updateRebill($subscription)) {
                    $subscription->addRelatedObject($quote, true);
                    $this->subscriptionRepository->save($subscription);
                    $this->messageManager->addSuccessMessage(__('Subscription saved.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Subscription error while sending request.'));
                }
            } else {
                $this->messageManager->addWarningMessage(__('No data were changes'));
            }

            if ($this->getRequest()->getParam('back', false)) {
                $resultRedirect->setPath('*/*/edit', ['entity_id' => $subscription->getId(), '_current' => true]);
            } else {
                $resultRedirect->setPath('*/*/index');
            }
        } catch (\Exception $e) {
//            $this->helper->log('subscriptions', (string)$e);
            $this->messageManager->addErrorMessage(__('ERROR: %1', $e->getMessage()));

            $resultRedirect->setPath('*/*/edit', ['entity_id' => $subscription->getId(), '_current' => true]);
        }

        return $resultRedirect;
    }
}
