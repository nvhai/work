<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Controller;

use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Api\RebillManagementInterface;
use IWD\BluePaySubs\Model\Source\Agent;
use Magento\Framework\App\Action\Context;
use IWD\BluePay\Gateway\Request\PaymentDataBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Bsubs
 * @package IWD\BluePaySubs\Controller
 */
abstract class Bsubs extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface
     */
    protected $subscriptionRepository;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var RebillManagementInterface
     */
    protected $rebillManagement;

    /**
     * Bsubs constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param RebillManagementInterface $rebillManagement
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        RebillManagementInterface $rebillManagement
    ) {
        parent::__construct(
            $context
        );
        $this->registry = $registry;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->currentCustomer = $currentCustomer;
        $this->rebillManagement = $rebillManagement;
    }

    /**
     * Initialize subscription model for the current request.
     *
     * @return bool Successful or not
     */
    protected function _init()
    {
        /**
         * Load subscription by ID.
         */
        $id = (int)$this->getRequest()->getParam('id');

        try {
            /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
            $subscription = $this->subscriptionRepository->getById($id);
        } catch (\Exception $e) {
            return false;
        }

        $customerId = $this->currentCustomer->getCustomerId();

        /**
         * If it doesn't exist, or isn't ours, fail (redirect to grid).
         */
        if ($id < 1 || $subscription->getId() != $id || $subscription->getCustomerId() != $customerId) {
            return false;
        }

        $this->registry->register('current_subs', $subscription);

        return true;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param string $logMessage
     * @return $this
     * @throws LocalizedException
     */
    public function updateRebill(SubscriptionInterface $subscription, $logMessage = '')
    {
        if ($this->rebillManagement->updateRebill($subscription)) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $subscription->getQuote();
            $subscription->addRelatedObject($quote, true);
            if($logMessage) {
                $subscription->addLog($logMessage, ['agent_id' => Agent::AGENT_CUSTOMER]);
            }
            $this->subscriptionRepository->save($subscription);
            $this->messageManager->addSuccessMessage(__('Subscription saved.'));
        } else {
            throw new LocalizedException(
                __('Something wrong during update your subscription on gateway service. Please, contact our store admin')
            );
        }

        return $this;
    }
}
