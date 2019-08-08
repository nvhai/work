<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier;

/**
 * Class DefaultModifier
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier
 */
class DefaultModifier implements ModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public function modify($metadata, $addressType)
    {
        return $metadata;
    }
}
