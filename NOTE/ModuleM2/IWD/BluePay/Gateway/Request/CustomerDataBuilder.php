<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use IWD\BluePay\Gateway\SubjectReader;

/**
 * Class CustomerDataBuilder
 */
class CustomerDataBuilder implements BuilderInterface
{
    /**
     * Customer block name
     */
    const CUSTOMER = 'customer';

    /**
     * The first name value must be less than or equal to 255 characters.
     */
    const FIRST_NAME = 'firstName';

    /**
     * The last name value must be less than or equal to 255 characters.
     */
    const LAST_NAME = 'lastName';

    /**
     * The customer’s company. 255 character maximum.
     */
    const COMPANY = 'companyName';

    /**
     * The customer’s email address, comprised of ASCII characters.
     */
    const EMAIL = 'email';

    /**
     * Phone number. Phone must be 10-14 characters and can
     * only contain numbers, dashes, parentheses and periods.
     */
    const PHONE = 'phone';

    /**
     * The customer’s country
     */
    const COUNTRY = 'country';

    /**
     * The customer’s city
     */
    const CITY = 'city';

    /**
     * The customer’s state
     */
    const STATE = 'state';

    /**
     * The customer’s street
     */
    const ADDRESS_1 = 'addr1';

    /**
     * The customer’s additional address
     */
    const ADDRESS_2 = 'addr2';

    /**
     * The customer’s zip
     */
    const ZIP = 'zip';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

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
     * @param SubjectReader $subjectReader
     * @param CountryFactory $countryFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        SubjectReader $subjectReader,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();
        $countryId = $billingAddress->getCountryId();
        $country = $this->countryFactory->create()->loadByCode($countryId);
        $region = $this->regionFactory->create()->loadByCode($billingAddress->getRegionCode(), $countryId);

        return [
            self::CUSTOMER => [
                self::FIRST_NAME => $billingAddress->getFirstname(),
                self::LAST_NAME => $billingAddress->getLastname(),
                self::COMPANY => $billingAddress->getCompany(),
                self::PHONE => $billingAddress->getTelephone(),
                self::EMAIL => $billingAddress->getEmail(),
                self::COUNTRY => $country->getName(),
                self::CITY => $billingAddress->getCity(),
                self::STATE => $region->getName(),
                self::ADDRESS_1 => $billingAddress->getStreetLine1(),
                self::ADDRESS_2 => $billingAddress->getStreetLine2(),
                self::ZIP => $billingAddress->getPostcode()
            ]
        ];
    }
}
