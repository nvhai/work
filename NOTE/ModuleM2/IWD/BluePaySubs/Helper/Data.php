<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Helper;

use IWD\BluePaySubs\Setup\InstallData;
use Magento\Framework\App\Helper\AbstractHelper;
use IWD\BluePay\Helper\Json;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use IWD\BluePaySubs\Model\Source\Period;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Setup\UpgradeData;

/**
 * General helper
 */
class Data extends AbstractHelper
{
    const SUBS_ONETIME_TITLE = 'One Time';

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfig;

    /**
     * @var array
     */
    protected $quoteContainsSubscription = [];

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Period
     */
    protected $periodSource;

    /**
     * Required fields for SUBS_OPTIONS
     *
     * @var array
     */
    private $subsOptionsFields = [
        'period_interval', 'period', 'cycles'
    ];

    /**
     * @var array
     */
    private $itemSubscription = [];

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration\Proxy $productConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Product\Configuration\Proxy $productConfig,
        Json $serializer,
        Period $periodSource,
        PriceCurrencyInterface $priceCurrency
    )
    {
        parent::__construct($context);
        $this->productConfig = $productConfig;
        $this->serializer = $serializer;
        $this->periodSource = $periodSource;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Check whether the given quote contains a subscription item.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function quoteContainsSubscription($quote)
    {
        if (($quote instanceof \Magento\Quote\Api\Data\CartInterface) !== true) {
            return false;
        }

        if ($quote->getId() && isset($this->quoteContainsSubscription[$quote->getId()])) {
            return $this->quoteContainsSubscription[$quote->getId()];
        } else {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllItems() as $item) {
                if ($this->isItemSubscription($item) === true) {
                    if ($quote->getId()) {
                        $this->quoteContainsSubscription[$quote->getId()] = true;
                    }

                    return true;
                }
            }

            if ($quote->getId()) {
                $this->quoteContainsSubscription[$quote->getId()] = false;
            }
        }

        return false;
    }

    /**
     * Check whether subscriptions module is enabled in configuration for the current scope.
     *
     * @return bool
     */
    public function moduleIsActive()
    {
        return $this->scopeConfig->getValue(
            'iwd_subs/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ? true : false;
    }

    /**
     * Get subscription option ID by product
     *
     * @return string|null
     */
    public function getSubscriptionOptionId(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData(UpgradeData::SUBS_PRODUCT_OPTION_ID) ?: null;
    }

    /**
     * Get label for the subscription custom option. Poor attempt at flexibility/localization.
     *
     * @return string
     */
    public function getSubscriptionLabel()
    {
        return (string)__(
            $this->scopeConfig->getValue(
                'iwd_subs/general/option_label',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }

    /**
     * @return bool
     */
    public function canCustomerStop()
    {
        return (bool)$this->scopeConfig->getValue(
            'iwd_subs/general/allow_customer_cancel',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function canCustomerEdit()
    {
        return (bool)$this->scopeConfig->getValue(
            'iwd_subs/general/allow_customer_edit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getCustomerGridLimit()
    {
        return (int)$this->scopeConfig->getValue(
            'iwd_subs/general/customer_grid_limit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getCustomerLink()
    {
        return (string)$this->scopeConfig->getValue(
            'iwd_subs/general/customer_sidebar_link',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config allowed payment failed run count
     *
     * @return int
     */
    public function getPaymentFailedRunCount()
    {
        return (int)$this->scopeConfig->getValue('iwd_subs/general/payment_failed_run_count');
    }

    /**
     * Get config payment failed period
     *
     * @return int
     */
    public function getPaymentFailedRetryPeriod()
    {
        return (int)$this->scopeConfig->getValue('iwd_subs/general/payment_failed_retry_period');
    }

    /**
     * Check whether the given item should be a subscription.
     *
     * @param ExtensibleDataInterface $item
     * @return bool
     */
    public function isItemSubscription(ExtensibleDataInterface $item)
    {
        /** @var \Magento\Sales\Model\Order\Item|\Magento\Quote\Model\Quote\Item $item */
        if ($item->getProduct()->getData(InstallData::SUBS_ACTIVE) == 1) {
            if ($this->getItemSubscriptionInterval($item) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the subscription description for the given item.
     *
     * @param ExtensibleDataInterface $item
     * @return string
     */
    public function getItemSubscriptionDescription(ExtensibleDataInterface $item)
    {
        /** @var \Magento\Sales\Model\Order\Item|\Magento\Quote\Model\Quote\Item $item */
        return $item->getName();
    }

    /**
     * Calculate initial price for a subscription item.
     *
     * @param ExtensibleDataInterface $item
     * @return float
     */
    public function getItemSubscriptionPrice(ExtensibleDataInterface $item)
    {
        $product = $item->getProduct();
        $price = $product->getFinalPrice();
        $subsOption = $this->getItemSubscription($item);

        return !empty($subsOption) ? max(0, floatval($subsOption['price'])) : $price;
    }

    /**
     * Get the subscription interval (if any) for the current item. 0 for none.
     *
     * @param ExtensibleDataInterface $item
     * @return int
     */
    public function getItemSubscriptionInterval(ExtensibleDataInterface $item)
    {
        $subsOption = $this->getItemSubscription($item);

        return !empty($subsOption) ? intval($subsOption['period_interval']) : 0;
    }

    /**
     * @param ExtensibleDataInterface $item
     * @return mixed|string
     */
    public function getItemSubscriptionPeriod(ExtensibleDataInterface $item)
    {
        $subsOption = $this->getItemSubscription($item);

        return !empty($subsOption) ? $subsOption['period'] : Period::PERIOD_DAY;
    }

    /**
     * @param ExtensibleDataInterface $item
     * @return array|null
     */
    public function getItemSubscription(ExtensibleDataInterface $item)
    {
        if ($item instanceof \Magento\Quote\Model\Quote\Item) {
            $options = $this->productConfig->getCustomOptions($item);
        } else {
            $options = $item->getProductOptions();
            $options = isset($options['options']) ? $options['options'] : [];
        }
        $product = $item->getProduct();

        foreach ($options as $option) {
            if ($this->getSubscriptionOptionId($product) == $option['option_id'] ||
                $option['label'] == $this->getSubscriptionLabel()) {
                $this->itemSubscription[$item->getId()] = $this->getSubscriptionByTitle($product, $option['value']);
                return $this->itemSubscription[$item->getId()];
            }
        }

        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $title
     * @return array|null
     */
    protected function getSubscriptionByTitle(\Magento\Catalog\Model\Product $product, $title)
    {
        if (!empty($product->getData(InstallData::SUBS_OPTIONS))) {
            $subsOptions = $this->unserialize($product->getData(InstallData::SUBS_OPTIONS));
            foreach ($subsOptions as $subsOption) {
                if ($this->generateOptionTitle($subsOption) == $title) {
                    return $subsOption;
                }
            }
        }

        return null;
    }

    /**
     * @param array $option
     * @param bool $showPrice
     * @return \Magento\Framework\Phrase|string
     */
    public function generateOptionTitle(array $option, $showPrice = true)
    {
        $title = '';
        if (!$this->isValidSubsOption($option)) {
            return $title;
        }
        $periodInterval = intval($option['period_interval']);
        if ($periodInterval <= 0) {
            return $title;
        }
        $cycles = intval($option['cycles']);
        $price = isset($option['price']) ? floatval($option['price']) : 0;
        $price = empty($price) && isset($option['amount']) ? floatval($option['amount']) : $price;
        $period = strtolower($option['period']);
        $periodLabel = strtolower($this->periodSource->getOptionText($period));
        $periodPlural = strtolower($this->periodSource->getOptionTextPlural($period));

        if ($periodInterval == 1) {
            $title = __('Every ' . $periodLabel);
        } else {
            $title = __('Every %1 ' . $periodPlural, $periodInterval);
        }

        if ($cycles) {
            $title .= __(' %1 times', $cycles);
        }

        if ($price && $showPrice) {
            $title .= __(' (%1)', $this->priceCurrency->format($price, false));
        }

        if (isset($option['shipping_price']) && $showPrice) {
            $title .= __(' + %1 shipping', $this->priceCurrency->format($option['shipping_price'], false));
        }

        return $title;
    }

    /**
     * Validate subscription option
     *
     * @param array $option
     * @return bool
     */
    public function isValidSubsOption(array $option)
    {
        foreach ($this->subsOptionsFields as $field) {
            if (!isset($option[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $interval
     * @param string $period
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getDateInterval($interval, $period, $format = 'Y-m-d')
    {
        $expDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $expDate->add(new \DateInterval('P' . $interval . strtoupper($period[0])));

        return $expDate->format($format);
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function serialize($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * @param $data
     * @return array|bool|float|int|null|string
     */
    public function unserialize($data)
    {
        return $this->serializer->unserialize($data);
    }

    /**
     * Retrieve subscription product options or current subscription value
     *
     * @return int | array
     */
    public function getFrequencies(SubscriptionInterface $subscription)
    {
        $frequencies = [];
        if ($product = $this->getProduct($subscription)) {
            if ($attrOptions = $product->getCustomAttribute(InstallData::SUBS_OPTIONS)) {
                $frequencies = $this->unserialize($attrOptions->getValue());
            }
        }

        return $frequencies;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param bool $withShippingPrice
     * @return array
     */
    public function getFrequenciesOptionArray(SubscriptionInterface $subscription, $withShippingPrice = false)
    {
        $result = [];
        if ($options = $this->getFrequencies($subscription)) {
            foreach ($options as $id => $option) {
                if ($withShippingPrice) {
                    $shippingAmount = $subscription->getQuote()->getShippingAddress()->getShippingAmount();
                    if ($shippingAmount > 0) {
                        $option['shipping_price'] = $shippingAmount;
                    }
                }
                $label = $this->generateOptionTitle($option);
                $result[$id] = $label;
            }
        }

        return $result;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return int|null
     */
    public function getCurrentFrequencyId(SubscriptionInterface $subscription)
    {
        $result = null;
        $currentLabel = $this->generateOptionTitle($subscription->getData(), false);
        if ($options = $this->getFrequencies($subscription)) {
            foreach ($options as $id => $option) {
                $label = $this->generateOptionTitle($option, false);
                if ($currentLabel == $label) {
                    $result = $id;
                }
            }
        }

        return $result;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param $frequencyId
     * @return array
     */
    public function getFrequencyById(SubscriptionInterface $subscription, $frequencyId)
    {
        if ($options = $this->getFrequencies($subscription)) {
            foreach ($options as $id => $option) {
                if (intval($frequencyId) === $id) {
                    return $option;
                }
            }
        }

        return [];
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return null | \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct(SubscriptionInterface $subscription)
    {
        if (!$subscription) {
            return null;
        }

        if ($quote = $subscription->getQuote()) {
            $items = $quote->getAllVisibleItems();
            $item = array_shift($items);
            if (is_object($item)) {
                return $item->getProduct();
            }
        }

        return null;
    }
}
