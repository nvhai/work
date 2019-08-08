<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Plugin\BluePay\Model\Ui;

use Magento\Checkout\Model\Session as CheckoutSession;
use IWD\BluePaySubs\Helper\Data as Helper;

/**
 * Class ConfigProvider
 * @package IWD\BluePaySubs\Plugin\BluePay\Model\Ui
 */
class ConfigProvider
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * ConfigProvider constructor.
     * @param Helper $helper
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Helper $helper,
        CheckoutSession $checkoutSession
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \IWD\BluePay\Model\Ui\ConfigProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetConfig(
        \IWD\BluePay\Model\Ui\ConfigProvider $subject,
        $result
    ) {
        if ($this->helper->moduleIsActive() && $this->helper->quoteContainsSubscription($this->getQuote())) {
            $forceSaveConfig = [
                'payment' => [
                    $subject::CODE => [
                        'forceSaveInVault' => 1,
                    ]
                ]
            ];
            $result = array_replace_recursive($result, $forceSaveConfig);
        }

        return $result;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}