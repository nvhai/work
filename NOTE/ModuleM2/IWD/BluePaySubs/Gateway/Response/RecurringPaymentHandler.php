<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Gateway\Response;

use IWD\BluePay\Api\AdapterResponseInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use IWD\BluePaySubs\Model\Subscription;
use IWD\BluePaySubs\Api\SubscriptionRepositoryInterface;
use IWD\BluePaySubs\Model\Source\Status;
use IWD\BluePaySubs\Helper;
use IWD\BluePay\Gateway\Request;
use IWD\BluePay\Gateway\SubjectReader;
use IWD\BluePaySubs\Api\SubsAdapterResponseInterface;
use IWD\BluePaySubs\Api\RebillManagementInterface;

class RecurringPaymentHandler implements HandlerInterface
{
    /**
     * @var Helper\Data
     */
    protected $helper;

    /**
     * @var Helper\Vault
     */
    protected $vaultHelper;

    /**
     * @var \IWD\BluePaySubs\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartInterfaceFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterfaceFactory
     */
    protected $quoteAddressFactory;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $objectCopyService;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Request\CustomerDataBuilder
     */
    private $customerDataBuilder;
    /**
     * @var Request\PaymentDataBuilder
     */
    private $paymentDataBuilder;

    /**
     * @var RebillManagementInterface
     */
    private $rebillManagement;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    public function __construct(
        Helper\Data $helper,
        Helper\Vault $vaultHelper,
        \IWD\BluePaySubs\Model\SubscriptionFactory $subscriptionFactory,
        \Magento\Quote\Api\Data\CartInterfaceFactory $quoteFactory,
        \Magento\Quote\Api\Data\AddressInterfaceFactory $quoteAddressFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        SubjectReader $subjectReader,
        Request\CustomerDataBuilder $customerDataBuilder,
        Request\PaymentDataBuilder $paymentDataBuilder,
        RebillManagementInterface $rebillManagement,
        SubscriptionRepositoryInterface $subscriptionRepository
    )
    {
        $this->helper = $helper;
        $this->vaultHelper = $vaultHelper;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->customerRepository = $customerRepository;
        $this->objectCopyService = $objectCopyService;
        $this->eventManager = $eventManager;
        $this->subjectReader = $subjectReader;
        $this->customerDataBuilder = $customerDataBuilder;
        $this->paymentDataBuilder = $paymentDataBuilder;
        $this->rebillManagement = $rebillManagement;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Create subscriptions as needed on order place.
     *
     * @inheritdoc
     */
    public function handle(array $subject, array $response)
    {
        if ($this->helper->moduleIsActive() !== true) {
            return;
        }
        /** @var AdapterResponseInterface $responseObj */
        $responseObj = $response['object'];
        if ($responseObj->getData(SubsAdapterResponseInterface::REBILL_ID)) {
            // Subscription was generated yet, response contain rebill ID
            return;
        }

        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $data = $this->customerDataBuilder->build($subject);
        $data += $this->paymentDataBuilder->build($subject);
        $publicHash = false;
        /** @var OrderItemInterface $item */
        foreach ($order->getAllVisibleItems() as $item) {
            try {
                if (!$this->helper->isItemSubscription($item)) {
                    continue;
                }
                $shippingAmount = $order->getShippingAmount();
                if ($rebillResponse = $this->rebillManagement->createRebill($data, $item, $shippingAmount)) {
                    $subscription = $this->generateSubscription($item, $rebillResponse);

                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $this->generateSubscriptionQuote($order, $item);
                    $this->prepareVaultData($quote, $order, $rebillResponse);
                    $subscription->setStoreId($quote->getStoreId())
                        ->setCustomerId($quote->getCustomerId())
                        ->setQuote($quote)
                        ->setAmount($item->getPrice() * $item->getQtyOrdered() + $shippingAmount);
                    if (!$publicHash) {
                        $publicHash = $quote->getPayment()->getAdditionalInformation('public_hash');
                    }
                    $quote->getPayment()->setAdditionalInformation('public_hash', $publicHash);

                    $subscription->addRelatedObject($quote, true);
                    $message = __(
                        'Subscription created. Initial order total: %1',
                        $order->formatPriceTxt($order->getGrandTotal())
                    );
                    $subscription->addLog($message, ['order_increment_id' => $order->getIncrementId()]);
                    $this->subscriptionRepository->save($subscription);
                }

            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * Create a subscription for the given item.
     *
     * @param OrderItemInterface $item
     * @param SubsAdapterResponseInterface $response
     * @return Subscription
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function generateSubscription(
        OrderItemInterface $item,
        SubsAdapterResponseInterface $response
    )
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionFactory->create();

        $subscription->setRebillId($response->getRebillId())
            ->setTransactionId($response->getTransactionId())
            ->setNextDate($response->getRebillingFirstDate())
            ->setStatus(Status::STATUS_ACTIVE)
            ->setPeriodInterval($this->helper->getItemSubscriptionInterval($item))
            ->setPeriod($this->helper->getItemSubscriptionPeriod($item))
            ->setDescription($this->helper->getItemSubscriptionDescription($item))
            ->setCycles($response->getCycles())
            ->setCyclesRunCount(0)
            ->setAdditionalInformation('message', $response->getMessage());

        if ($response->getCycles()) {
            $subscription->setCycles($response->getCycles());
        }

        return $subscription;
    }

    /**
     * Create a subscription base quote for the given item.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function generateSubscriptionQuote(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\OrderItemInterface $item
    )
    {
        /**
         * Initialize objects
         */

        /** @var \Magento\Sales\Model\Order\Item $item */
        /** @var \Magento\Sales\Model\Order $order */

        /** @var \Magento\Quote\Model\Quote $orderQuote */
        $orderQuote = $this->quoteFactory->create();
        $orderQuote->load($order->getQuoteId());

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
            $orderQuote->getBillingAddress(),
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
            $orderQuote->getShippingAddress(),
            $shippingAddress
        );

        /**
         * Duplicate payment object
         */

        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_payment',
            'to_quote_payment',
            $order->getPayment(),
            $quote->getPayment()
        );

        $quote->getPayment()->setId(null);
        $quote->getPayment()->setQuoteId(null);

        /**
         * Duplicate customer info
         */
        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_customer',
            'to_quote',
            $order,
            $quote
        );

        // Try to load and set customer.
        $customerId = $order->getCustomerId();

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

        // Set a far-off quote updated date to avoid pruning. This is the highest Magento allows (timestamp).
        $updatedAt = new \DateTime('2038-01-01');

        $quote->setStoreId($order->getStoreId())
            ->setIsMultiShipping(false)
            ->setIsActive(false)
            ->setUpdatedAt($updatedAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT))
            ->setRemoteIp($order->getRemoteIp())
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress);

        $product = $item->getProduct();

        if (!$product->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not find product for item %1 (%2)', $item->getSku(), $item->getId())
            );
        }

        /**
         * Set the product and price
         */
        $info = $item->getProductOptionByCode('info_buyRequest');
        $info = new \Magento\Framework\DataObject($info);
        $info->setData('qty', $item->getQtyOrdered());

        $quote->addProduct($product, $info);

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $quote->getItemsCollection()->getFirstItem();

        $newPrice = $this->helper->getItemSubscriptionPrice($quoteItem);

        if ($newPrice != $product->getFinalPrice()) {
            $quoteItem->setOriginalCustomPrice($newPrice);
        }

        /**
         * Set shipping info
         */

        $quote->setIsVirtual($quote->getIsVirtual());


        $quote->getShippingAddress()->setShippingMethod($order->getShippingMethod())
            ->setShippingAmount($order->getShippingAmount())
            ->setShippingDescription($order->getShippingDescription());

        $quote->getShippingAddress()->setCollectShippingRates(true)
            ->collectShippingRates();

        $quote->collectTotals();

        return $quote;
    }

    /**
     * If the payment method is not TokenBase, convert it to its proper vault form for later.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareVaultData(
        \Magento\Quote\Api\Data\CartInterface $quote,
        \Magento\Sales\Api\Data\OrderInterface $order,
        SubsAdapterResponseInterface $response
    )
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        /** @var \Magento\Sales\Model\Order $order */
        if ($this->vaultHelper->isQuoteTokenBase($quote) === false) {
            $payment = $quote->getPayment();
            $method = $payment->getMethod();

            if (strpos($method, '_cc_vault') === false) {
                $payment->setMethod($method . '_cc_vault');
            }

            $publicHash = $payment->getAdditionalInformation('public_hash');

            if (empty($publicHash)) {
                // We're missing the vault info. Fetch and store it.
                $token = $this->vaultHelper->getVaultPaymentToken($response->getTransactionId(), $order->getPayment());

                if ($token !== null) {
                    // The order must be saved to trigger vault hash generation.
                    // cf. \Magento\Vault\Observer\AfterPaymentSaveObserver::execute()
                    $order->save();
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Could not find payment token.')
                    );
                }
            }
            $extAttr = $this->vaultHelper->getExtensionAttributes($order->getPayment());
            $orderToken = $extAttr->getVaultPaymentToken();
            if (empty($orderToken)) {
                return $this;
            }
            // Upd. Quote payment
            $payment->setAdditionalInformation('customer_id', $orderToken->getCustomerId())
                ->setAdditionalInformation('public_hash', $orderToken->getPublicHash());
            $this->vaultHelper->addAdditionalDataToPayment($payment, $orderToken);
        }

        return $this;
    }

    /**
     * Get the Vault order payment extension (Vault card), if any.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentExtensionInterface|null $extensionAttributes
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface|null
     */
    protected function getVaultExtension(
        \Magento\Sales\Api\Data\OrderPaymentExtensionInterface $extensionAttributes = null
    )
    {
        if ($extensionAttributes === null) {
            return null;
        }

        $token = $extensionAttributes->getVaultPaymentToken();
        if ($token === null || empty($token->getGatewayToken())) {
            return null;
        }

        return $token;
    }
}