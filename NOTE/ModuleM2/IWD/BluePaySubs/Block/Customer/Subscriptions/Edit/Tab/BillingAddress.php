<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Directory\Block\Data as DirectoryBlock;
use IWD\BluePaySubs\Helper\Data as Helper;
use IWD\BluePaySubs\Helper\Address as HelperAddress;
use IWD\BluePay\Model\Ui\ConfigProvider;

/**
 * Class BillingAddress
 * @package IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
 */
class BillingAddress extends \IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var HelperAddress
     */
    protected $addressHelper;

    /**
     * @var DirectoryBlock
     */
    protected $directoryBlock;

    /**
     * BillingAddress constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Helper $helper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigProvider $configProvider
     * @param HelperAddress $addressHelper
     * @param DirectoryBlock $directoryBlock
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        Helper $helper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        CustomerRepositoryInterface $customerRepository,
        ConfigProvider $configProvider,
        HelperAddress $addressHelper,
        DirectoryBlock $directoryBlock,
        array $data = []
    ) {
        parent::__construct($context, $registry, $helper, $priceCurrency, $data);
        $this->customerRepository = $customerRepository;
        $this->configProvider = $configProvider;
        $this->addressHelper = $addressHelper;
        $this->directoryBlock = $directoryBlock;
    }

    /**
     * @return array
     */
    public function getAddresses()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->getCurrentSubscription();
        $options = [
            [
                'label' => __("Add New Address"),
                'value' => 0
            ]
        ];

        try {
            if ($subscription->getCustomerId()) {
                $customer = $this->customerRepository->getById($subscription->getCustomerId());

                foreach ($customer->getAddresses() as $address) {
                    $options[] = [
                        'label' => $this->getFormattedAddress($address),
                        'value' => $address->getId()
                    ];
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $options;
    }

    /**
     * Get HTML-formatted card address. This is silly, but it's how the core says to do it.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @param string $format
     * @return string
     * @see \Magento\Customer\Model\Address\AbstractAddress::format()
     */
    public function getFormattedAddress(\Magento\Customer\Api\Data\AddressInterface $address, $format = 'flat')
    {
        return $this->addressHelper->getFormattedAddress($address, $format);
    }

    /**
     * @inheritdoc
     */
    public function getCurrentAddress()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->getCurrentSubscription();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();

        return  $quote->getBillingAddress();
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        try {
            $config = [
                'addresses' => $this->getAddresses()
            ];
            if ($active = $this->getCurrentAddress()) {
                $activeAddressId = $active->getCustomerAddressId();
                if ($activeAddressId !== NULL){
                    $config['selectedAddressId'] = $activeAddressId;
                }
                else {
                    foreach ($this->getCustomerAddresses() as $address) {
                        if($this->addressHelper->compareAddresses($address, $this->getCurrentAddress())){
                            $config['selectedAddressId'] = $address->getId();
                        }
                    }
                }
            }

            $config = $this->helper->serialize($config);

        } catch (\Exception $e) {
            $config = '';
        }

        return (string) $config;
    }

    /**
     * @return string
     */
    public function getSummaryTabInfo()
    {
        $result = ' ';
        try {
            $currentAddress = $this->getCurrentAddress();
            $addresses = $this->getAddresses();
            foreach ($addresses as $address) {
                if ($address['value'] == $currentAddress->getCustomerAddressId() && $address['value'] !== 0) {
                    $result = $address['label'];
                }
            }

            if($result == ' ') {
                foreach ($this->getCustomerAddresses() as $address) {
                    if($this->addressHelper->compareAddresses($address, $currentAddress)){
                        $result = $this->getFormattedAddress($address);
                    }
                }

                if($result == ' ') {
                    $q = $currentAddress->getData();
                    $result = $q['firstname'].' '.$q['lastname'].', '.$q['street'].', '.$q['city'].', '.$q['region'].' '.$q['postcode'].', '.$q['country_id'];
                }
            }
        } catch(\Exception $e) {
            $result = ' ';
        }

        return $result;
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface[]|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerAddresses()
    {
        $subscription = $this->getCurrentSubscription();
        $customer = $this->customerRepository->getById($subscription->getCustomerId());

        return $customer->getAddresses();
    }

    /**
     * @inheritdoc
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/bsubs_edit/billingAddress', ['id' => $this->getCurrentSubscription()->getId()]);
    }

    /**
     * @param null $defValue
     * @param string $name
     * @param string $id
     * @param string $title
     * @return string
     */
    public function getCountryHtmlSelect(
        $defValue = null,
        $name = 'country_id',
        $id = 'billing-country',
        $title = 'Billing Country'
    ) {
        return $this->directoryBlock->getCountryHtmlSelect($defValue, $name, $id, $title);
    }
}