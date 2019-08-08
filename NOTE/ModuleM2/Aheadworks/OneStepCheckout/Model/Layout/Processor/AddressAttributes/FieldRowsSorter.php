<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor\AddressAttributes;

use Aheadworks\OneStepCheckout\Model\Address\Form\FieldRow\DefaultSortOrder;
use Aheadworks\OneStepCheckout\Model\Config;

/**
 * Class FieldRowsSorter
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor\AddressAttributes
 */
class FieldRowsSorter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DefaultSortOrder
     */
    private $defaultSortOrder;

    /**
     * @param Config $config
     * @param DefaultSortOrder $defaultSortOrder
     */
    public function __construct(
        Config $config,
        DefaultSortOrder $defaultSortOrder
    ) {
        $this->config = $config;
        $this->defaultSortOrder = $defaultSortOrder;
    }

    /**
     * Sort field rows
     *
     * @param array $config
     * @param array $addressType
     * @return array
     */
    public function sort(array $config, $addressType)
    {
        $formConfig = $this->config->getAddressFormConfig($addressType);
        foreach ($config as $rowId => &$rowConfig) {
            if (isset($formConfig['rows'][$rowId])) {
                $rowConfig['sortOrder'] = $formConfig['rows'][$rowId]['sort_order'];
            } else {
                $defaultSortOrder = $this->defaultSortOrder->getSortOrder($rowId);
                if ($defaultSortOrder !== null) {
                    $config[$rowId]['sortOrder'] = $defaultSortOrder;
                }
            }
        }
        return $config;
    }
}
