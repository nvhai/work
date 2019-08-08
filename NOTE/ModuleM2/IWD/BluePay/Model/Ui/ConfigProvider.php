<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\CcConfig;
use Magento\Framework\View\Asset\Source;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use IWD\BluePay\Api\CardRepositoryInterface;

/**
 * Class ConfigProvider
 * @package IWD\BluePay\Model\Ui
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'iwd_bluepay';

    const CC_VAULT_CODE = 'iwd_bluepay_cc_vault';

    const CC_VAULT_INTERNAL = 'internal_cc_vault_active';

    const PAYMENT_TYPE_AUTH = 'AUTH';

    const PAYMENT_TYPE_SALE = 'SALE';

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfiguration;

    /**
     * @var Source
     */
    private $assetSource;

    /**
     * @var CustomerSession
     */
    private $_customerSession;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CardRepositoryInterface
     */
    private $cardRepository;

    /**
     * @var array
     */
    private $customerData = ['name1', 'name2', 'company', 'street', 'city', 'region', 'zip', 'email'];

    /**
     * ConfigProvider constructor.
     * @param CcConfig $ccConfig
     * @param ScopeConfigInterface $scopeConfiguration
     * @param Source $assetSource
     * @param CustomerSession $customerSession
     * @param CustomerRegistry $customerRegistry
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     * @param CardRepositoryInterface $cardRepository
     */
    public function __construct(
        CcConfig $ccConfig,
        ScopeConfigInterface $scopeConfiguration,
        Source $assetSource,
        CustomerSession $customerSession,
        CustomerRegistry $customerRegistry,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        CardRepositoryInterface $cardRepository
    ) {
        $this->ccConfig = $ccConfig;
        $this->scopeConfiguration = $scopeConfiguration;
        $this->assetSource = $assetSource;
        $this->_customerSession = $customerSession;
        $this->customerRegistry = $customerRegistry;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->cardRepository = $cardRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $method = self::CODE;

        $transType = $this->getStoreConfig('payment_action') == "authorize"
            ? self::PAYMENT_TYPE_AUTH : self::PAYMENT_TYPE_SALE;
        $accountId = $this->getStoreConfig('account_id');
        $hashstr = $this->getStoreConfig('secret_key') . $accountId . $this->getStoreConfig('trans_mode');
        $tps = hash('sha512', $hashstr);
        $customerData = $this->getCustomerData();

        $config = [
            'payment' => [
                $method => [
                    'accountId' => $accountId,
                    'tps' => $tps,
                    'tpsDef' => "MERCHANT MODE",
                    'transType' => $transType,
                    'transMode' => $this->getStoreConfig('trans_mode'),
                    'cctypes' => $this->getStoreConfig('cctypes'),
                    'availableTypes' => $this->getCcAvailableTypes(),
                    'months' => $this->getCcMonths(),
                    'years' => $this->getCcYears(),
                    'hasVerification' => $this->hasVerification(),
                    'hasSsCardType' => $this->hasSsCardType(),
                    'ssStartYears' => $this->getSsStartYears(),
                    'cvvImageUrl' => $this->getCvvImageUrl(),
                    'active' => $this->getStoreConfig('active'),
                    'paymentTypes' => $this->getStoreConfig('payment_type'),
                    'isShowPaymentType' => $transType == self::PAYMENT_TYPE_SALE,
                    'allowAccountsStorage' => $this->getStoreConfig('tokenization'),
                    'storedAccounts' => $this->getStoredAccounts(),
                    'isCustomerLoggedIn' => $this->_customerSession->isLoggedIn(),
                    'enableIframe' => $this->getStoreConfig('use_iframe'),
                    'iframeUrl' => 'https://secure.bluepay.com/interfaces/shpf?SHPF_FORM_ID=magento2',
                    'useCvv2' => $this->getStoreConfig('useccv'),
                    'level3' => $this->getLevel3(),
                    'quoteData' => $this->getQuote()->getData(),
                    'customerName1' => $customerData['name1'],
                    'customerName2' => $customerData['name2'],
                    'customerCompany' => $customerData['company'],
                    'customerStreet' => $customerData['street'],
                    'customerCity' => $customerData['city'],
                    'customerRegion' => $customerData['state'],
                    'customerZip' => $customerData['zip'],
                    'customerEmail' => $customerData['email'],
                    'isInternalVaultEnabled' => $this->isInternalVaultActive(),
                    'forceSaveInVault' => 0,
                    'forceSaveInVaultMessage' => '',
                    'vaultCode' => self::CC_VAULT_CODE
                ],
                'ccform' => [
                    'availableTypes' => ['iwd_bluepay' => $this->getCcAvailableTypes()]
                ]
            ]
        ];
        return $config;
    }

    /**
     * Solo/switch card start years
     *
     * @return array
     */
    public function getSsStartYears()
    {
        return $this->ccConfig->getSsStartYears();
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        return $this->ccConfig->getCcMonths();
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        return $this->ccConfig->getCcYears();
    }

    /**
     * Retrieve CVV tooltip image url
     *
     * @return string
     */
    public function getCvvImageUrl()
    {
        return $this->ccConfig->getCvvImageUrl();
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->ccConfig->getCcAvailableTypes();
        $availableTypes = $this->getStoreConfig("cctypes");
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach (array_keys($types) as $code) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve has verification configuration
     *
     * @return bool
     */
    public function hasVerification()
    {
        $result = $this->ccConfig->hasVerification();
        $configData = $this->getStoreConfig("useccv");
        if ($configData !== null) {
            $result = (bool)$configData;
        }
        return $result;
    }

    /**
     * Whether switch/solo card type available
     *
     * @return bool
     */
    public function hasSsCardType()
    {
        $result = false;
        $availableTypes = explode(',', $this->getStoreConfig("cctypes"));
        $ssPresentations = array_intersect(['SS', 'SM', 'SO'], $availableTypes);
        if ($availableTypes && count($ssPresentations) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerData()
    {
        $data = $this->customerData;
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            $data['name1'] = $this->getBillingAddress()->getFirstName();
            $data['name2'] = $this->getBillingAddress()->getLastName();
            $data['company'] = $this->getBillingAddress()->getCompany() != null ? $this->getBillingAddress()->getCompany() : '';
            $data['street'] = $this->getBillingAddress()->getStreet()[0];
            $data['city'] = $this->getBillingAddress()->getCity() != null ? $this->getBillingAddress()->getCity() : '';
            $data['state'] = $this->getBillingAddress()->getRegion();
            $data['zip'] = $this->getBillingAddress()->getPostCode();
            $data['email'] = $this->getBillingAddress()->getEmail();
        } else {
            $customer = $this->customerRegistry->retrieve($customerId);
            $customerData = $customer->getDataModel();
            $addresses = $customerData->getAddresses();
            $data['name1'] = $addresses != null ? $addresses[0]->getFirstName() : '';
            $data['name2'] = $customerData->getAddresses() != null ? $addresses[0]->getLastName() : '';
            $data['company'] = $addresses != null && $addresses[0]->getCompany() != null ? $addresses[0]->getCompany() : '';
            $data['street'] = $addresses != null ? $addresses[0]->getStreet()[0] : '';
            $data['city'] = $addresses != null ? $addresses[0]->getCity() : '';
            $data['state'] = $addresses != null ? $addresses[0]->getRegion()->getRegionCode() : '';
            $data['zip'] = $addresses != null ? $addresses[0]->getPostCode() : '';
            $data['email'] = $customerData->getEmail();
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getStoredAccounts()
    {
        $options = [];
        if(!$this->isInternalVaultActive()) {
            return $options;
        }
        try {
            $customerId = $this->getCustomerId();
            $savedCcList = $this->cardRepository->getSavedCcListForCustomer($customerId);
            foreach ($savedCcList as $hash => $savedCc) {
                $options[] = [
                    'label' => $savedCc,
                    'value' => $hash,
                ];
            }
        } catch (LocalizedException $e) {
            $options = [];
        }

        return $options;
    }

    /**
     * @return bool
     */
    private function isInternalVaultActive()
    {
        $method = self::CC_VAULT_CODE;
        $vaultStatus = $this->scopeConfiguration->getValue("payment/{$method}/active",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $internalVaultStatus = $this->getStoreConfig(self::CC_VAULT_INTERNAL);
        return (bool) !$vaultStatus && $internalVaultStatus;
    }

    /**
     * @return array
     */
    private function getLevel3()
    {
        $i = 1;
        $level3 = [];
        foreach ($this->getQuote()->getAllItems() as $item) {
            $level3["LV3_ITEM".$i."_PRODUCT_CODE"] = htmlentities($item->getSku());
            $level3["LV3_ITEM".$i."_UNIT_COST"] = $item->getPrice();
            $level3["LV3_ITEM".$i."_QUANTITY"] = $item->getQty();
            $level3["LV3_ITEM".$i."_ITEM_DESCRIPTOR"] = htmlentities($item->getName());
            $level3["LV3_ITEM".$i."_MEASURE_UNITS"] = 'EA';
            $level3["LV3_ITEM".$i."_COMMODITY_CODE"] = '-';
            $level3["LV3_ITEM".$i."_TAX_AMOUNT"] = round($item->getPrice() * ($item->getTaxPercent() / 100), 2);
            $level3["LV3_ITEM".$i."_TAX_RATE"] = $item->getTaxPercent() . '%';
            $level3["LV3_ITEM".$i."_ITEM_DISCOUNT"] = '';
            $level3["LV3_ITEM".$i."_LINE_ITEM_TOTAL"] = $item->getPrice() * $item->getQty();
            $i++;
        }

        return $level3;
    }

    /**
     * @return int
     */
    private function getCustomerId()
    {
        return $this->getQuote()->getCustomerId();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address
     */
    private function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * @param $field
     * @return mixed|string
     */
    private function getStoreConfig($field)
    {
        $result = '';
        if($field) {
            $method = self::CODE;
            $result = $this->scopeConfiguration->getValue(
                "payment/{$method}/{$field}",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $result;
    }
}