<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier;

/**
 * Interface ModifierInterface
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier
 */
interface ModifierInterface
{
    /**
     * Modify attribute metadata
     *
     * @param array $metadata
     * @param string $addressType
     * @return array
     */
    public function modify($metadata, $addressType);
}
