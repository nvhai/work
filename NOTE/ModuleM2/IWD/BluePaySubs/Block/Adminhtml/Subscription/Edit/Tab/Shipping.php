<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Block\Adminhtml\Subscription\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Shipping tab
 */
class Shipping extends \Magento\Customer\Block\Address\Edit implements TabInterface
{
    /**
     * @var \Magento\Customer\Api\Data\AddressInterface
     */
    protected $shippingAddress;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \IWD\BluePaySubs\Helper\Address
     */
    protected $addressHelper;

    /**
     * @var \IWD\BluePaySubs\Model\Service\CurrencyManager
     */
    protected $currencyManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession *Proxy
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer *Proxy
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\BluePaySubs\Helper\Address $addressHelper
     * @param \IWD\BluePaySubs\Model\Service\CurrencyManager $currencyManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Helper\Address $addressHelper,
        \IWD\BluePaySubs\Model\Service\CurrencyManager $currencyManager,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->addressHelper = $addressHelper;
        $this->currencyManager = $currencyManager;

        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $data
        );
    }

    /**
     * Get current subscription model
     *
     * @return \IWD\BluePaySubs\Api\Data\SubscriptionInterface
     */
    public function getSubscription()
    {
        $subscription = $this->registry->registry('current_bsubs');

        return $subscription;
    }

    /**
     * Return the associated address.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        if ($this->shippingAddress === null) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->getSubscription()->getQuote();

            $this->shippingAddress = $quote->getShippingAddress()->exportCustomerAddress();
        }

        return $this->shippingAddress;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->getAddress()->getStreet();

        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        /*if ($this->currentCustomer !== null) {
            return $this->currentCustomer;
        }*/

        $registryCustomer = $this->registry->registry('current_customer');

        if ($registryCustomer instanceof \Magento\Customer\Model\Customer) {
            $this->currentCustomer = $registryCustomer->getDataModel();
        } elseif ($registryCustomer instanceof \Magento\Customer\Api\Data\CustomerInterface) {
            $this->currentCustomer = $registryCustomer;
        } else {
            $this->currentCustomer = parent::getCustomer();
        }

        return $this->currentCustomer;
    }

    /**
     * Get HTML-formatted card address. This is silly, but it's how the core says to do it.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @param string $format
     * @return string
     * @see \Magento\Customer\Model\Address\AbstractAddress::format()
     */
    public function getFormattedAddress(\Magento\Customer\Api\Data\AddressInterface $address, $format = 'html')
    {
        return $this->escapeHtml($this->addressHelper->getFormattedAddress($address, $format));
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Shipping');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Shipping');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get quote estimated shipping rates.
     *
     * @return array
     */
    public function getShippingMethods()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getSubscription()->getQuote();

        return $quote->getShippingAddress()->getGroupedAllShippingRates();
    }

    /**
     * Check whether the subscription's assigned shipping method is actually available.
     *
     * @return bool
     */
    public function isValidShippingMethod()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getSubscription()->getQuote();

        $method = $quote->getShippingAddress()->getShippingRateByCode(
            $quote->getShippingAddress()->getShippingMethod()
        );

        return $method !== false;
    }

    /**
     * Convert and format shipping rate price.
     *
     * @param float $cost
     * @return string
     */
    public function getShippingMethodPrice($cost)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getSubscription()->getQuote();

        $price = $this->currencyManager->convertPriceCurrency(
            $cost,
            $quote->getBaseCurrencyCode(),
            $quote->getQuoteCurrencyCode()
        );

        $currency = $this->currencyManager->getCurrencyByCode(
            $quote->getQuoteCurrencyCode()
        );

        return $currency->formatTxt($price);
    }
}
