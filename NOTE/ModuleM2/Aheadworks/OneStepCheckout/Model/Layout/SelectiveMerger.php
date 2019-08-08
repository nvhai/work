<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout;

/**
 * Class SelectiveMerger
 * @package Aheadworks\OneStepCheckout\Model\Layout
 */
class SelectiveMerger
{
    /**
     * @var RecursiveMerger
     */
    private $recursiveMerger;

    /**
     * @param RecursiveMerger $recursiveMerger
     */
    public function __construct(RecursiveMerger $recursiveMerger)
    {
        $this->recursiveMerger = $recursiveMerger;
    }

    /**
     * Fetch components definitions and merge into config
     *
     * @param array $config
     * @param array $sourceConfig
     * @param array $toMerge
     * @return array
     */
    public function merge(array $config, array $sourceConfig, array $toMerge)
    {
        foreach ($sourceConfig as $code => $sourceConfigData) {
            if (in_array($code, $toMerge)) {
                if (!isset($config[$code])) {
                    $config[$code] = [];
                }
                $config[$code] = $this->recursiveMerger->merge($config[$code], $sourceConfigData);
            }
        }
        return $config;
    }
}
