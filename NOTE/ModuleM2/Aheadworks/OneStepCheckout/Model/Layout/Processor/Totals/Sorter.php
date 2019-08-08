<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor\Totals;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Sorter
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor\Totals
 */
class Sorter
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Sort totals
     *
     * @param array $config
     * @return array
     */
    public function sort(array $config)
    {
        $sortData = $this->scopeConfig->getValue('sales/totals_sort');
        foreach ($config as $code => &$configData) {
            $sortTotalCode = str_replace('-', '_', $code);
            if (isset($sortData[$sortTotalCode]) && isset($config[$code])) {
                $configData['sortOrder'] = $sortData[$sortTotalCode];
            }
        }
        return $config;
    }
}
