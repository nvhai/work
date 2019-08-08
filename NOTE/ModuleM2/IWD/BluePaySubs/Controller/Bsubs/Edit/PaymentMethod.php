<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Controller\Bsubs\Edit;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Api\RebillManagementInterface;
use IWD\BluePaySubs\Model\Service\PaymentInfoBuilder;
use IWD\BluePaySubs\Helper\Vault as VaultHelper;

/**
 * PaymentMethod Class
 */
class PaymentMethod extends \IWD\BluePaySubs\Controller\Bsubs
{
    /**
     * @var \IWD\BluePaySubs\Model\Source\Status
     */
    protected $statusSource;

    /**
     * @var \IWD\BluePaySubs\Model\Service\Subscription
     */
    protected $subscriptionService;

    /**
     * @var VaultHelper
     */
    protected $vaultHelper;

    /**
     * PaymentMethod constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \IWD\BluePaySubs\Model\Service\Subscription $subscriptionService
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \IWD\BluePaySubs\Model\Service\Subscription $subscriptionService,
        RebillManagementInterface $rebillManagement,
        VaultHelper $vaultHelper
    ) {
        parent::__construct($context, $registry, $subscriptionRepository, $currentCustomer, $rebillManagement);
        $this->subscriptionService = $subscriptionService;
        $this->vaultHelper = $vaultHelper;
    }

    /**
     * Subscriptions edit page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $initialized = $this->_init();
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($initialized !== true) {
            $resultRedirect->setPath('*/bsubs/index');
            return $resultRedirect;
        }
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->registry->registry('current_subs');

        try {
            if (!isset($params['payment'])) {
                throw new LocalizedException(__('Error, no payment method specified'));
            }
            $this->updateSubscriptionPayment($subscription, $params['payment']);
            $this->updateRebill($subscription, 'Payment method changed.');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

        }
        $resultRedirect->setPath('*/bsubs/edit', ['id' => $subscription->getId()]);

        return $resultRedirect;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param array $paymentData
     * @throws LocalizedException
     * @throws \Exception
     * @return $this
     */
    protected function updateSubscriptionPayment(SubscriptionInterface $subscription, array $paymentData)
    {
        if (!empty($paymentData['public_hash'])) {
            // Update payment
            $this->subscriptionService->changePaymentAccount($subscription, $paymentData['public_hash']);
            $this->messageManager->addSuccessMessage(__('Payment account changed.'));
        } elseif (!empty($paymentData['cc_number'])) {
            // Create payment
            $this->subscriptionService->createPaymentAccount($subscription, $paymentData);
            $this->messageManager->addSuccessMessage(__('Payment card authorized.'));
        } else {
            throw new LocalizedException(__('Error, no payment method specified.'));
        }

        return $this;
    }
}
