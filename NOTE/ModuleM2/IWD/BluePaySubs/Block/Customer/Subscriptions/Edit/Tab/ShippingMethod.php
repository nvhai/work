<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab;

/**
 * Class ShippingMethod
 * @package IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
 */
class ShippingMethod extends \IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
{
    /**
     * @var array
     */
    protected $rates = [];

    /**
     * @return array
     * @throws \Exception
     */
    public function getShippingRates()
    {
        if(!empty($this->rates)) {
            return $this->rates;
        }
        $address = $this->getShippingAddress();
        $address->setCollectShippingRates(true)
            ->collectShippingRates();
        /** @var \Magento\Quote\Model\Quote\Address\Rate[] $rates */
        $this->rates = $address->getAllShippingRates();

        return $this->rates;
    }

    /**
     * @param bool $inclPrice
     * @return array
     */
    public function getShippingRateOptions($inclPrice = true)
    {
        $options = [];
        try {
            foreach ($this->getShippingRates() as $rate) {
                $title = $rate->getCarrierTitle() . ': ' . $rate->getMethodTitle();
                if ($inclPrice && $rate->getPrice() > 0) {
                    $price = $this->priceCurrency->format($rate->getPrice(), false);
                    $title .= " (+$price)";
                }
                $options[] = [
                    'label' => $title,
                    'value' => $rate->getCode()
                ];
            }
        } catch(\Exception $e) {
            $options = [];
        }

        return $options;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address
     * @throws \Exception
     */
    public function getShippingAddress()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->getCurrentSubscription();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();

        return $quote->getShippingAddress();
    }

    /**
     * @param bool $inclPrice
     * @return array|mixed
     * @throws \Exception
     */
    public function getCurrentRateOption($inclPrice = true)
    {
        if ($active = $this->getShippingAddress()->getShippingMethod()) {
            foreach ($this->getShippingRateOptions($inclPrice) as $rateOption) {
                if($rateOption['value'] == $active) {
                    return $rateOption;
                }
            }
        }

        return [];
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function getConfig()
    {
        $config = [
            'shippingRates' => $rates = $this->getShippingRateOptions(),
            'formId' => $this->escapeHtml($this->getFormId())
        ];
        $active = $this->getCurrentRateOption();
        if (!empty($active)) {
            $config['currentRate'] = $active['value'];
        }

        return $this->helper->serialize($config);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getSummaryTabInfo()
    {
        $active = $this->getCurrentRateOption(false);
        if (!empty($active)) {
            return $active['label'];
        }

        return '';
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/bsubs_edit/shippingMethod', ['id' => $this->getCurrentSubscription()->getId()]);
    }
}