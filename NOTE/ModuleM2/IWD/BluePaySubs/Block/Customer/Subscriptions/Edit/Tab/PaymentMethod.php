<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab;

use IWD\BluePaySubs\Helper\Data as Helper;
use IWD\BluePaySubs\Helper\Vault as HelperVault;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use IWD\BluePay\Model\Ui\ConfigProvider;

/**
 * Class PaymentMethod
 * @package IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
 */
class PaymentMethod extends \IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
{
    /**
     * @var HelperVault
     */
    protected $vaultHelper;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * PaymentMethod constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Helper $helper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param HelperVault $vaultHelper
     * @param ConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        Helper $helper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        HelperVault $vaultHelper,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $registry, $helper, $priceCurrency, $data);
        $this->vaultHelper = $vaultHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * @return array
     */
    public function getStoredAccounts()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->getCurrentSubscription();
        $options = [
            [
                'label' => __("Add New Card"),
                'value' => 0
            ]
        ];

        try {
            if ($subscription->getCustomerId()) {
                /** @var PaymentTokenInterface[] $tokens */
                $tokens = $this->vaultHelper->searchTokens(['customer_id' => $subscription->getCustomerId()]);
                foreach ($tokens as $token) {
                    $options[] = [
                        'label' => $this->vaultHelper->getTokenLabel($token),
                        'value' => $token->getPublicHash()
                    ];
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $options;
    }

    /**
     * @return PaymentTokenInterface|null
     * @throws \Exception
     */
    public function getCurrentToken()
    {
        $token = null;
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->getCurrentSubscription();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();

        $activeToken = $this->vaultHelper->getQuoteToken($quote);
        if (!empty($activeToken) && $activeToken instanceof PaymentTokenInterface) {
            $token = $activeToken;
        }

        return $token;
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function getConfig()
    {
        $config = [
            'storedAccounts' => $this->getStoredAccounts(),
            'availableTypes' => $this->configProvider->getCcAvailableTypes(),
            'months' => $this->configProvider->getCcMonths(),
            'years' => $this->configProvider->getCcYears(),
        ];
        if($active = $this->getCurrentToken()) {
            $config['hashStoredAccount'] = $active->getPublicHash();
        }

        return $this->helper->serialize($config);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getSummaryTabInfo()
    {
        $label = '';
        if($active = $this->getCurrentToken()) {
            $label = $this->vaultHelper->getTokenLabel($active) . ', Expires ' .
                date(
                    'm/Y',
                    strtotime($active->getExpiresAt())
                );
        }

        return $label;
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/bsubs_edit/paymentMethod', ['id' => $this->getCurrentSubscription()->getId()]);
    }
}