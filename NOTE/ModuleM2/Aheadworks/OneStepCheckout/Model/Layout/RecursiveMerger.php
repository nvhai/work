<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout;

/**
 * Class RecursiveMerger
 * @package Aheadworks\OneStepCheckout\Model\Layout
 */
class RecursiveMerger
{
    /**
     * Perform recursive config merging
     *
     * @param array $target
     * @param array $source
     * @return array
     */
    public function merge(array $target, array $source)
    {
        foreach ($source as $key => $value) {
            if (is_array($value)) {
                if (!isset($target[$key])) {
                    $target[$key] = [];
                }
                $target[$key] = $this->merge($target[$key], $value);
            } else {
                $target[$key] = $value;
            }
        }
        return $target;
    }
}
