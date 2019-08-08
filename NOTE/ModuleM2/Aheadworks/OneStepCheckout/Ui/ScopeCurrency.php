<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui;

use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\StoreId as StoreFilter;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\StoreGroupId as StoreGroupFilter;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\WebsiteId as WebsiteFilter;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ScopeCurrency
 * @package Aheadworks\OneStepCheckout\Ui
 */
class ScopeCurrency
{
    /**
     * @var StoreFilter
     */
    private $storeFilter;

    /**
     * @var StoreGroupFilter
     */
    private $storeGroupFilter;

    /**
     * @var WebsiteFilter
     */
    private $websiteFilter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param StoreFilter $storeFilter
     * @param StoreGroupFilter $storeGroupFilter
     * @param WebsiteFilter $websiteFilter
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreFilter $storeFilter,
        StoreGroupFilter $storeGroupFilter,
        WebsiteFilter $websiteFilter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeFilter = $storeFilter;
        $this->storeGroupFilter = $storeGroupFilter;
        $this->websiteFilter = $websiteFilter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get scope currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeCode = null;

        $websiteId = $this->websiteFilter->getValue();
        $groupId = $this->storeGroupFilter->getValue();
        $storeId = $this->storeFilter->getValue();

        if ($websiteId) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $websiteId;
        } elseif ($groupId) {
            $scopeType = ScopeInterface::SCOPE_GROUP;
            $scopeCode = $groupId;
        } elseif ($storeId) {
            $scopeType = ScopeInterface::SCOPE_STORE;
            $scopeCode = $storeId;
        }

        return $this->scopeConfig->getValue(
            Currency::XML_PATH_CURRENCY_DEFAULT,
            $scopeType,
            $scopeCode
        );
    }
}
