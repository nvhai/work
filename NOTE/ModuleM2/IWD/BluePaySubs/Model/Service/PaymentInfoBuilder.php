<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Model\Service;

use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use IWD\BluePay\Gateway\Request\PaymentDataBuilder;
use IWD\BluePay\Gateway\Request\CustomerDataBuilder;

/**
 * Class PaymentInfoBuilder
 * @package IWD\BluePaySubs\Model\Service
 */
class PaymentInfoBuilder
{
    /**
     * @var array
     */
    private $paymentKeyMap = [
        PaymentDataBuilder::CARD_EXPIRE => [
            'cc_exp_month' => 2,
            'cc_exp_year' => 2
        ],
        PaymentDataBuilder::CARD_NUMBER => 'cc_number',
        PaymentDataBuilder::CVV2 => 'cc_cid',
        PaymentDataBuilder::PAYMENT_TYPE => 'payment_type'
    ];

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * CustomerDataBuilder constructor.
     * @param CountryFactory $countryFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        CountryFactory $countryFactory,
        RegionFactory $regionFactory
    ) {
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param array $data
     * @return array
     */
    public function buildBillingCardInfo(array $data)
    {
        $result = [];
        foreach ($this->paymentKeyMap as $requestKey => $map) {
            $value = '';
            if(is_array($map)) {
                foreach ($map as $mapField => $length) {
                    if(isset($data[$mapField])) {
                        $num = substr($data[$mapField], -$length);
                        $value .= sprintf("%0{$length}d", $num);
                    }
                }
            }
            elseif(isset($data[$map])) {
                $value = $data[$map];
            }
            $result[$requestKey] = str_replace(' ', '', $value);
        }

        return [PaymentDataBuilder::PAYMENT => $result];
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return array
     * @throws \Exception
     */
    public function buildBillingAddressInfo(SubscriptionInterface $subscription)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();
        if(!$quote) {
            return [];
        }
        $billingAddress = $quote->getBillingAddress();
        $countryId = $billingAddress->getCountryId();
        $country = $this->countryFactory->create()->loadByCode($countryId);
        $region = $this->regionFactory->create()->loadByCode($billingAddress->getRegionCode(), $countryId);

        return [
            CustomerDataBuilder::CUSTOMER => [
                CustomerDataBuilder::FIRST_NAME => $billingAddress->getFirstname(),
                CustomerDataBuilder::LAST_NAME => $billingAddress->getLastname(),
                CustomerDataBuilder::COMPANY => $billingAddress->getCompany(),
                CustomerDataBuilder::PHONE => $billingAddress->getTelephone(),
                CustomerDataBuilder::EMAIL => $billingAddress->getEmail(),
                CustomerDataBuilder::COUNTRY => $country->getName(),
                CustomerDataBuilder::CITY => $billingAddress->getCity(),
                CustomerDataBuilder::STATE => $region->getName(),
                CustomerDataBuilder::ADDRESS_1 => $billingAddress->getStreetLine1(),
                CustomerDataBuilder::ADDRESS_2 => $billingAddress->getStreetLine2(),
                CustomerDataBuilder::ZIP => $billingAddress->getPostcode()
            ]
        ];
    }
}