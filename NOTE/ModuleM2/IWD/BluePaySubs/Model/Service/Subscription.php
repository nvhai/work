<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Service;

use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Api\LogRepositoryInterface;
use IWD\BluePaySubs\Api\RebillManagementInterface;
use IWD\BluePaySubs\Api\SubsAdapterResponseInterface;
use IWD\BluePaySubs\Api\SubscriptionManagementInterface;
use IWD\BluePaySubs\Api\SubscriptionRepositoryInterface;
use IWD\BluePaySubs\Gateway\Request\RebillDataBuilder;
use IWD\BluePaySubs\Model\Source\Period;
use IWD\BluePaySubs\Model\Source\Status;
use IWD\BluePaySubs\Plugin\Payment\Model\Method\Adapter as PaymentAdapterPlugin;
use Magento\Framework\Exception\LocalizedException;

/**
 * Subscription service model: Common actions to be performed on subscriptions.
 */
class Subscription implements SubscriptionManagementInterface
{
    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    private $objectCopyService;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\Quote\Api\Data\CartInterfaceFactory
     */
    private $quoteFactory;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterfaceFactory
     */
    private $quoteAddressFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender\Proxy
     */
    private $orderSender;

    /**
     * @var \IWD\BluePaySubs\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulator;

    /**
     * @var \IWD\BluePaySubs\Helper\Vault
     */
    private $vaultHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var RebillManagementInterface
     */
    private $rebillManagement;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @var PaymentInfoBuilder
     */
    private $paymentInfoBuilder;

    /**
     * Subscription constructor.
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Quote\Model\QuoteManagement\Proxy $quoteManagement
     * @param \Magento\Quote\Api\Data\CartInterfaceFactory $quoteFactory
     * @param \Magento\Quote\Api\Data\AddressInterfaceFactory $quoteAddressFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender\Proxy $orderSender
     * @param \Psr\Log\LoggerInterface $logger
     * @param \IWD\BluePaySubs\Helper\Data $helper
     * @param \Magento\Store\Model\App\Emulation $emulator
     * @param \IWD\BluePaySubs\Helper\Vault $vaultHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param LogRepositoryInterface $logRepository
     * @param RebillManagementInterface $rebillManagement
     * @param EmailManager $emailManager
     */
    public function __construct(
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Quote\Model\QuoteManagement\Proxy $quoteManagement,
        \Magento\Quote\Api\Data\CartInterfaceFactory $quoteFactory,
        \Magento\Quote\Api\Data\AddressInterfaceFactory $quoteAddressFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender\Proxy $orderSender,
        \Psr\Log\LoggerInterface $logger,
        \IWD\BluePaySubs\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulator,
        \IWD\BluePaySubs\Helper\Vault $vaultHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        SubscriptionRepositoryInterface $subscriptionRepository,
        LogRepositoryInterface $logRepository,
        RebillManagementInterface $rebillManagement,
        EmailManager $emailManager,
        PaymentInfoBuilder $paymentInfoBuilder
    )
    {
        $this->objectCopyService = $objectCopyService;
        $this->customerFactory = $customerFactory;
        $this->quoteManagement = $quoteManagement;
        $this->quoteFactory = $quoteFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->customerRepository = $customerRepository;
        $this->eventManager = $eventManager;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->emulator = $emulator;
        $this->vaultHelper = $vaultHelper;
        $this->messageManager = $messageManager;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->logRepository = $logRepository;
        $this->rebillManagement = $rebillManagement;
        $this->emailManager = $emailManager;
        $this->paymentInfoBuilder = $paymentInfoBuilder;
    }

    /**
     * Subscription synchronize process
     *
     * @param SubscriptionInterface $subscription
     * @param int $agentId
     * @return $this
     * @throws \Exception
     */
    public function synchronize(SubscriptionInterface $subscription, $agentId = 0)
    {
        // Request to payment API
        $rebillStatus = $this->rebillManagement->getRebillStatus($subscription);
        if (!$rebillStatus) {
            throw new \Exception(__('Sorry, subscription not found on payment gateway'));
        }
        try {
            $this->_updateSubscription($subscription, $rebillStatus);
            // important ! we generate orders after update subscription
            $lastRunDate = $subscription->getLastDate();
            if (empty($lastRunDate)) {
                $lastRunDate = date("d-m-Y H:i:s", strtotime($subscription->getCreatedAt()));
            } else {
                $lastRunDate = date("d-m-Y H:i:s", strtotime($lastRunDate));
            }
            $nextDate = date("d-m-Y H:i:s", strtotime($rebillStatus->getLastDate()));
            if (strtotime($nextDate) >= strtotime($lastRunDate)) {
                if (isset($rebillStatus['template_id'])) {
                    $this->_generateOrders($subscription);
                    $message = __(
                        'Subscription synchronized. Rebilling last run date %1',
                        $subscription->getLastDate()
                    );
                    $subscription->addLog($message, ['agent_id' => $agentId]);
                }
            }

            if ($rebillStatus->getStatus() == Status::STATUS_PAYMENT_FAILED) {
                // Try 'Payment Failed' trigger to 'Active' status
                $this->_reactivateSubscription($subscription, $agentId);
            }

        } catch (\Exception $e) {
            $message = __(
                'Subscription synchronize error. %1',
                $e->getMessage()
            );
            $subscription->addLog($message, ['agent_id' => $agentId]);
            throw new \Exception($e->getMessage());
        } finally {
            $this->subscriptionRepository->save($subscription);
        }

        return $this;
    }

    /**
     * Create subscription payment account
     *
     * @param SubscriptionInterface $subscription
     * @param array $paymentData
     * @param $agentId
     * @throws LocalizedException
     * @throws \Exception
     * @return $this
     */
    public function createPaymentAccount(SubscriptionInterface $subscription, array $paymentData, $agentId = 0)
    {
        $rebillPaymentData = $this->paymentInfoBuilder->buildBillingCardInfo($paymentData);
        $rebillPaymentData += $this->paymentInfoBuilder->buildBillingAddressInfo($subscription);
        $paymentResponse = $this->rebillManagement->createRebillPayment($rebillPaymentData);
        $subscription->setTransactionId($paymentResponse->getTransactionId());

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();
        $payment = $quote->getPayment();
        $payment->setData('echeck_type', 'CC');
        foreach (['cc_exp_month', 'cc_exp_year', 'cc_type'] as $field) {
            if (isset($paymentData[$field])) {
                $payment->setData($field, $paymentData[$field]);
            }
        }
        $payment->setAdditionalInformation('PAYMENT_ACCOUNT', substr($paymentData['cc_number'], -4));
        $paymentToken = $this->vaultHelper->generateToken($subscription->getTransactionId(), $quote->getPayment());
        $paymentToken->setCustomerId($quote->getCustomerId())
            ->setIsActive(true)
            ->setIsVisible(true)
            ->setPaymentMethodCode('iwd_bluepay')
            ->setPublicHash($this->vaultHelper->generatePublicHash($paymentToken));
        $payment->setAdditionalInformation('customer_id', $paymentToken->getCustomerId())
            ->setAdditionalInformation('public_hash', $paymentToken->getPublicHash());
        $paymentToken->save();

        $subscription->addLog(
            __('Payment account authorized %1.', $this->vaultHelper->getTokenLabel($paymentToken)),
            ['agent_id' => $agentId]
        );

        return $this;
    }

    /**
     * Change subscription payment account
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param string $hash
     * @param $agentId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePaymentAccount(
        SubscriptionInterface $subscription,
        $hash,
        $agentId = 0
    )
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();

        $activeToken = $this->vaultHelper->getQuoteToken($quote);

        $token = $this->vaultHelper->getTokenByHash($hash);
        // token with new hash not found
        if (empty($token)) {
            return $this;
        }
        // token has the same hash as active
        if (!empty($activeToken) && $activeToken->getPublicHash() == $token->getPublicHash()) {
            return $this;
        }

        // Token must related to the same customer
        if ($token->getCustomerId() == $subscription->getCustomerId()) {
            // API will send request further
            $subscription->setTransactionId($token->getGatewayToken());
            $payment = $quote->getPayment();
            $payment->setAdditionalInformation('customer_id', $token->getCustomerId())
                ->setAdditionalInformation('public_hash', $token->getPublicHash());
            $method = $payment->getMethod();
            if (strpos($method, '_cc_vault') === false) {
                $payment->setMethod($method . '_cc_vault');
            }
            $this->vaultHelper->addAdditionalDataToPayment($payment, $token);

            $subscription->addRelatedObject($quote->getBillingAddress(), true);
            $subscription->addRelatedObject($quote->getPayment(), true);

            $subscription->addLog(
                __('Payment account changed to %1.', $this->vaultHelper->getTokenLabel($token)),
                ['agent_id' => $agentId]
            );
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid payment ID.')
            );
        }

        return $this;
    }

    /**
     * Change subscription billing address to the given data.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param array $data Array of address info
     * @param int $agentId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function changeBillingAddress(
        SubscriptionInterface $subscription,
        $data,
        $agentId = 0
    )
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();

        if ($data['address_id'] > 0) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->customerFactory->create();
            $customer->load($subscription->getCustomerId());

            if ($customer->getId() != $subscription->getCustomerId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Unable to load subscription customer.')
                );
            }

            $address = $customer->getAddressById($data['address_id']);

            if ($address instanceof \Magento\Customer\Model\Address
                && $address->getId() == $data['address_id']
                && $address->getCustomerId() == $customer->getId()) {
                $source = $address;
                $source->setData('customer_address_id', $address->getId());
            } else {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Please choose a valid billing address.')
                );
            }
        } else {
            $source = $data;
        }

        $this->objectCopyService->copyFieldsetToTarget(
            'sales_copy_order_billing_address',
            'to_order',
            $source,
            $quote->getBillingAddress()
        );

        $billingAddress = $quote->getBillingAddress();

        $data = $billingAddress->getData();
        foreach ($data as $key => $value) {
            if (!is_object($value) && $billingAddress->getOrigData($key) != $value && $key != 'region') {
                $quote->getBillingAddress()->validate();

                $subscription->addLog(
                    __('Billing address changed.'),
                    ['agentId' => $agentId]
                );

                $subscription->addRelatedObject($billingAddress, true);

                break;
            }
        }

        return $this;
    }

    /**
     * Change subscription shipping address to the given data.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param array $data Array of address info
     * @param int $agentId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function changeShippingAddress(
        SubscriptionInterface $subscription,
        $data,
        $agentId = 0
    )
    {
        $isEdited = false;
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();
        if (
            isset($data['address_id'])
            && $quote->getShippingAddress()->getCustomerAddressId()
            && $data['address_id'] != $quote->getShippingAddress()->getCustomerAddressId()
        ) {
            $isEdited = true;
        }

        if ($data['address_id'] == '') {
            $shippingAddressObject = $quote->getShippingAddress();
            if (
                $data['firstname'] != $shippingAddressObject->getData('firstname') ||
                $data['lastname'] != $shippingAddressObject->getData('lastname') ||
                $data['company'] != $shippingAddressObject->getData('company') ||
                $data['telephone'] != $shippingAddressObject->getData('telephone') ||
                $data['city'] != $shippingAddressObject->getData('city') ||
                $data['postcode'] != $shippingAddressObject->getData('postcode') ||
                $data['country_id'] != $shippingAddressObject->getData('country_id')
            ) {
                $isEdited = true;
            }
            if ($isEdited === false
            ) {
                $addressLines = '';
                foreach ($data['street'] as $key => $line) {
                    if ($key > 0 && $line != '') {
                        $addressLines .= ' ';
                    }
                    $addressLines .= $line;
                }
                if ($addressLines != $shippingAddressObject->getStreetFull()) {
                    $isEdited = true;
                }
                if ($isEdited == false && $data['region'] != '' && $data['region'] !== $shippingAddressObject->getData('region')) {
                    $isEdited = true;
                }
                if ($isEdited == false && $data['region_id'] != '' && $data['region_id'] !== $shippingAddressObject->getData('region_id')) {
                    $isEdited = true;
                }
            }
        }
        if ($data['address_id'] > 0) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->customerFactory->create();
            $customer->load($subscription->getCustomerId());

            if ($customer->getId() != $subscription->getCustomerId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Unable to load subscription customer.')
                );
            }

            $address = $customer->getAddressById($data['address_id']);

            if ($address instanceof \Magento\Customer\Model\Address
                && $address->getId() == $data['address_id']
                && $address->getCustomerId() == $customer->getId()) {
                $source = $address;
                $source->setData('customer_address_id', $address->getId());
            } else {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Please choose a valid shipping address.')
                );
            }
        } else {
            $source = $data;
        }

        $this->objectCopyService->copyFieldsetToTarget(
            'sales_copy_order_shipping_address',
            'to_order',
            $source,
            $quote->getShippingAddress()
        );

        $shippingAddress = $quote->getShippingAddress();

        $data = $shippingAddress->getData();
        foreach ($data as $key => $value) {
            if (!is_object($value) && $shippingAddress->getOrigData($key) != $value && $key != 'region') {
                $quote->getShippingAddress()->validate();
                if ($isEdited) {
                    $subscription->setDataChanges(true);
                    $subscription->addLog(
                        __('Shipping address changed.'),
                        ['agentId' => $agentId]
                    );
                }
                $subscription->addRelatedObject($shippingAddress, true);
                break;
            }
        }

        return $this;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param SubsAdapterResponseInterface $rebill
     * @return $this
     * @throws \Exception
     */
    protected function _updateSubscription(
        SubscriptionInterface $subscription,
        SubsAdapterResponseInterface $rebill
    )
    {
        $schedExpr = explode(' ', $rebill->getSchedExpr());

        if (count($schedExpr) < 2) {
            throw new \Exception(__('Subscription parsing error occurred.'));
        }

        $subscription->setTransactionId($rebill->getTemplateId())
            ->setAmount($rebill->getAmount())
            ->setNextDate($rebill->geNextDate())
            ->setLastDate($rebill->getLastDate())
            ->setPeriodInterval($schedExpr[0])
            ->setPeriod($schedExpr[1]);

        $this->_updateSubscriptionStatus($subscription, $rebill->getStatus());

        if (!empty($rebill->getCycles())) {
            $subscription->setCycles($rebill->getCycles());
        }

        return $this;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param $newStatus
     * @return $this
     */
    protected function _updateSubscriptionStatus(SubscriptionInterface $subscription, $newStatus)
    {
        $subscription->setStatus($newStatus);

        $this->_updateSubscriptionPaymentFailedCount($subscription);
        $configPaymentFailed = $this->helper->getPaymentFailedRunCount();
        if ($subscription->getPaymentFailedRunCount() >= $configPaymentFailed) {
            $this->rebillManagement->updateRebillStatus($subscription, Status::STATUS_STOPPED);
            $subscription->setStatus(Status::STATUS_STOPPED);
            $subscription->addLog(__('Retry period for Payment Failed subscription ended'));
        }

        return $this;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return $this
     */
    protected function _updateSubscriptionPaymentFailedCount(SubscriptionInterface $subscription)
    {
        $paymentFailedCount = $subscription->getPaymentFailedRunCount();
        if ($subscription->getStatus() == Status::STATUS_ACTIVE) {
            $paymentFailedCount = 0;
        } elseif ($subscription->getStatus() == Status::STATUS_PAYMENT_FAILED) {
            $paymentFailedCount += 1;
        }
        $subscription->setPaymentFailedRunCount($paymentFailedCount);

        return $this;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param $agentId
     * @return $this
     */
    protected function _reactivateSubscription(SubscriptionInterface $subscription, $agentId)
    {
        $lastRunDate = (new \DateTime($subscription->getLastDate()))
            ->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        $retryPeriod = $this->helper->getPaymentFailedRetryPeriod();
        $nextRunTime = strtotime(
            sprintf('+%s %s', $retryPeriod, Period::PERIOD_DAY),
            strtotime($lastRunDate)
        );
        if ($nextRunTime > time()) {
            return $this;
        }

        $nextRun = new \DateTime('@' . $nextRunTime);
        $subscription->setNextDate($nextRun->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
        $this->rebillManagement->updateRebill($subscription);
        $this->rebillManagement->updateRebillStatus($subscription, Status::STATUS_ACTIVE);
        $subscription->setStatus(Status::STATUS_ACTIVE);
        $subscription->addLog(
            __('Retry period for payment failed subscription triggered'),
            ['agent_id' => $agentId]
        );

        return $this;
    }

    /**
     * Generate order for the given subscription
     *
     * @param SubscriptionInterface $subscription
     * @return $this
     * @throws \Exception
     */
    protected function _generateOrders(SubscriptionInterface $subscription)
    {
        $this->emulator->startEnvironmentEmulation($subscription->getStoreId());
        try {
            $payedRebills = $this->_getSaledRebills($subscription);
            foreach ($payedRebills as $rebill) {
                $orderPayments = $this->vaultHelper->searchOrderPayment(['cc_trans_id' => $rebill->getData('id')]);
                // No payments found with transaction ID -> create new order
                if (empty($orderPayments)) {
                    // Initialize quote from subscription
                    $quote = $this->_generateBillingQuote($subscription);
                    $quote->getPayment()
                        ->setAdditionalInformation(RebillDataBuilder::LAST_TRANS_ID, $rebill->getData('id'))
                        ->setAdditionalInformation(RebillDataBuilder::LAST_TRANS_DATE, $rebill->getData('issue_date'))
                        ->setAdditionalInformation(PaymentAdapterPlugin::CAN_INITIALIZE_PAYMENT, 1);
                    try {
                        $this->_generateOrderInternal($subscription, $quote);
                    } catch (\Exception $e) {
                        //stop subscription cause order generation error
                        if ($this->rebillManagement->updateRebillStatus($subscription, Status::STATUS_STOPPED)) {
                            $subscription->setStatus(Status::STATUS_STOPPED);
                        }
                        //Email send order generation failed to admin
                        $this->emailManager->sendOrderGenerationFailedEmail($subscription, (string)$e->getMessage());
                        throw new \Exception($e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception((string)$e->getMessage());
        } finally {
            $this->emulator->stopEnvironmentEmulation();
        }


        return $this;
    }

    /**
     * Get subscription rebills saled (not settled) on gateway
     *
     * @param SubscriptionInterface $subscription
     * @return array
     * @throws \Exception
     */
    protected function _getSaledRebills(SubscriptionInterface $subscription)
    {
        $payedRebills = [];
        $lastDate = $subscription->getOrigData(SubscriptionInterface::LAST_DATE);
        if (empty($lastDate)) {
            $lastDate = $subscription->getCreatedAt();
        }
        $rebillSaleData = $this->rebillManagement->getRebillDailyReport($lastDate);
        foreach ($rebillSaleData as $rebill) {
            // Check rebill assoc with subscription
            if (!($rebill instanceof SubsAdapterResponseInterface) || empty($rebill->getData('rebilling_id'))
                || $rebill->getData('rebilling_id') != $subscription->getRebillId()
                || floatval($rebill->getData('amount')) <= 0) {
                continue;
            }
            if (empty($rebill->getData('status'))) {
                if (!$this->logRepository->getByTransactionId($rebill->getData('id'))) {
                    $message = __(
                        "Subscription #%1 : recurring payment error (message '%2')",
                        $subscription->getId(),
                        $rebill->getData('message')
                    );
                    $this->messageManager->addErrorMessage($message);
                    $subscription->addLog($message, ['transaction_id' => $rebill->getData('id')]);
                    //Email payment failed admin & customer
                    $this->emailManager->sendPaymentFailedEmail($subscription, $message)
                        ->sendBillingFailedEmail($subscription, $message);
                }
            } else {
                $payedRebills[] = $rebill;
            }
        }

        return $payedRebills;
    }

    /**
     * Generate order for the given subscription(s). If multiple given, they should all share the same
     * payment and shipping info.
     *
     * @param SubscriptionInterface $subscription
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _generateOrderInternal(
        SubscriptionInterface $subscription,
        \Magento\Quote\Model\Quote $quote
    )
    {
        /** @var \Magento\Quote\Model\Quote $subscriptionQuote */
        $subscriptionQuote = $subscription->getQuote();

        $quote->merge($subscriptionQuote);

        /**
         * Calculate shipping and totals
         */
        $quote->setIsVirtual($subscriptionQuote->getIsVirtual());

        $quote->getShippingAddress()->setCollectShippingRates(true)
            ->collectShippingRates();

        $quote->collectTotals();

        /**
         * Run the order
         */
        $this->eventManager->dispatch(
            'iwd_subs_order_generate_before',
            [
                'quote' => $quote,
                'subscription' => $subscription
            ]
        );

        if (!$quote->getAllItems()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("No product items in subscription quote.")
            );
        }

        $quote->save();

        /** @var \Magento\Sales\Model\Order $order */
        $orderData = [
            'subtotal' => $subscription->getAmount() - $quote->getShippingAddress()->getShippingAmount(),
            'base_subtotal' => $subscription->getAmount() - $quote->getShippingAddress()->getShippingAmount(),
            'base_grand_total' => $subscription->getAmount(),
            'grand_total' => $subscription->getAmount(),
            'total_due' => $subscription->getAmount(),
            'base_total_due' => $subscription->getAmount(),
            'total_invoiced' => $subscription->getAmount(),
            'base_total_invoiced' => $subscription->getAmount(),
        ];
        $order = $this->quoteManagement->submit($quote, $orderData);

        if (!($order instanceof \Magento\Sales\Api\Data\OrderInterface)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Failed to place order.")
            );
        }

        /**
         * Update post-order
         */
        $message = __(
            'Subscription #%1 billed. New order #%2 created. Order total: %3',
            $subscription->getId(),
            $order->getIncrementId(),
            $order->formatPriceTxt($order->getGrandTotal())
        );

        $this->messageManager->addSuccessMessage($message);

        $subscription->recordBilling($order, $message);

        $this->eventManager->dispatch(
            'iwd_subs_order_generate_after',
            [
                'order' => $order,
                'quote' => $quote,
                'subscription' => $subscription
            ]
        );

        /**
         * Send email
         */
        if ($order->getCanSendNewEmailFlag()) {
            try {
                $this->orderSender->send($order);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        return $this;
    }

    /**
     * Generate a new quote from the given subscription info.
     *
     * @param SubscriptionInterface $subscription
     * @return \Magento\Quote\Model\Quote
     * @throws \Exception
     */
    protected function _generateBillingQuote(SubscriptionInterface $subscription)
    {
        /**
         * Initialize objects
         */

        /** @var \Magento\Quote\Model\Quote $sourceQuote */
        $sourceQuote = $subscription->getQuote();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteFactory->create();

        /**
         * Duplicate billing address
         */

        /** @var \Magento\Quote\Model\Quote\Address $billingAddress */
        $billingAddress = $this->quoteAddressFactory->create();

        $this->objectCopyService->copyFieldsetToTarget(
            'sales_copy_order_billing_address',
            'to_order',
            $sourceQuote->getBillingAddress(),
            $billingAddress
        );

        /**
         * Duplicate shipping address
         */

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $this->quoteAddressFactory->create();

        $this->objectCopyService->copyFieldsetToTarget(
            'sales_copy_order_shipping_address',
            'to_order',
            $sourceQuote->getShippingAddress(),
            $shippingAddress
        );

        $shippingAddress->setShippingMethod($sourceQuote->getShippingAddress()->getShippingMethod())
            ->setShippingDescription($sourceQuote->getShippingAddress()->getShippingDescription());

        $payment = $quote->getPayment();
        /**
         * Duplicate payment object
         */
        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_payment',
            'to_quote_payment',
            $sourceQuote->getPayment(),
            $payment
        );
        $payment->setId(null)->setQuoteId(null);

        /**
         * Duplicate customer info
         */
        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_quote_customer',
            'to_quote',
            $sourceQuote,
            $quote
        );

        // Try to load and set customer.
        $customerId = $subscription->getCustomerId();

        if ($customerId > 0) {
            try {
                $customer = $this->customerRepository->getById($customerId);

                $quote->assignCustomer($customer);
            } catch (\Exception $e) {
                // Ignore missing customer error
            }
        }

        /**
         * Pull quote together
         */

        $now = new \DateTime('@' . time());

        $quote->setStoreId($sourceQuote->getStoreId())
            ->setIsMultiShipping(false)
            ->setIsActive(false)
            ->setUpdatedAt($now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT))
            ->setRemoteIp($sourceQuote->getRemoteIp())
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress);

        return $quote;
    }
}
