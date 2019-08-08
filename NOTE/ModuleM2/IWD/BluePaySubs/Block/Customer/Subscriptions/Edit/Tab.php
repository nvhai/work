<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Block\Customer\Subscriptions\Edit;

use Magento\Framework\View\Element\Template;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Helper\Data as Helper;

/**
 * Class Tab
 * @package IWD\BluePaySubs\Block\Customer\Subscriptions\Edit
 */
class Tab extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Tab constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Helper $helper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        Helper $helper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return string
     */
    public function getSummaryInfo()
    {
        $info = $this->getData('summary_info');
        if(is_array($info) && isset($info['child_tabs_delimiter']) && is_array($info['child_tabs_delimiter'])) {
            $data = [];
            foreach ($info['child_tabs_delimiter'] as $block => $limit) {
                /** @var \IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab $child */
                if($child = $this->getChildBlock($block)) {
                    $str = explode(', ', $child->getSummaryTabInfo());
                    for ($i = 0; $i < $limit; $i++) {
                        $data[] = isset($str[$i]) ? $str[$i] : '';
                    }
                }
            }
            $result = empty($data) ? '' : implode(', ', $data);
        }
        if(is_array($info) && isset($info['add_price'])){
            $data = (string) $this->getCurrentSubscription()->getAmount();
            $result = implode(', ', [$result, $this->priceCurrency->format($data, false)]);
        }
        if(is_string($info)) {
            $result = $info;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getSummaryTabInfo() {
        return '';
    }

    /**
     * @return null | SubscriptionInterface
     */
    public function getCurrentSubscription()
    {
        return $this->registry->registry('current_subs');
    }

    /**
     * @return bool
     */
    public function canCustomerEdit()
    {
        return (bool) $this->helper->canCustomerEdit();
    }

    /**
     * @return mixed
     */
    public function canShowShipping()
    {
        $quote = $this->getCurrentSubscription()->getQuote();
        return !$quote->getIsVirtual();
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        $canShow = $this->getData('can_show');
        return is_null($canShow) || $canShow;
    }
}